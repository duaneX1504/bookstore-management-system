<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function inputData(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') return $_POST;
    $body = json_decode($raw, true);
    if (!is_array($body)) respond(400, ['error' => 'Request body must be valid JSON.']);
    return $body;
}

function textField(array $data, string $key, int $max, bool $allowEmpty = false): string
{
    $value = trim((string)($data[$key] ?? ''));
    if ((!$allowEmpty && $value === '') || mb_strlen($value) > $max) {
        respond(422, ['error' => "$key is required and must be at most $max characters."]);
    }
    return $value;
}

function intField(array $data, string $key, int $min = 0, int $max = 2147483647): int
{
    $value = filter_var($data[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false || $value === null || $value < $min || $value > $max) {
        respond(422, ['error' => "$key must be a whole number from $min to $max."]);
    }
    return $value;
}

function definitions(): array
{
    return [
        'books' => [
            'table' => 'books', 'id' => 'book_id',
            'fields' => ['isbn','title','author_id','category_id','publisher','price','stock_qty'],
            'select' => 'SELECT b.book_id,b.isbn,b.title,b.author_id,a.name AS author_name,b.category_id,c.category_name,b.publisher,b.price,b.stock_qty FROM books b JOIN authors a ON a.author_id=b.author_id JOIN categories c ON c.category_id=b.category_id',
        ],
        'authors' => ['table'=>'authors','id'=>'author_id','fields'=>['name','email'],'select'=>'SELECT author_id,name,email FROM authors'],
        'categories' => ['table'=>'categories','id'=>'category_id','fields'=>['category_name'],'select'=>'SELECT category_id,category_name FROM categories'],
        'customers' => ['table'=>'customers','id'=>'customer_id','fields'=>['full_name','email','phone'],'select'=>'SELECT customer_id,full_name,email,phone FROM customers'],
    ];
}

function cleanRecord(string $resource, array $data): array
{
    if ($resource === 'books') {
        $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0 || $price > 999999.99) respond(422, ['error' => 'price must be a non-negative amount.']);
        return [
            'isbn' => textField($data, 'isbn', 20),
            'title' => textField($data, 'title', 180),
            'author_id' => intField($data, 'author_id', 1),
            'category_id' => intField($data, 'category_id', 1),
            'publisher' => textField($data, 'publisher', 120, true),
            'price' => number_format((float)$price, 2, '.', ''),
            'stock_qty' => intField($data, 'stock_qty', 0),
        ];
    }
    if ($resource === 'authors') {
        $email = textField($data, 'email', 150, true);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) respond(422, ['error' => 'Enter a valid author email address.']);
        return ['name'=>textField($data,'name',120),'email'=>$email === '' ? null : $email];
    }
    if ($resource === 'categories') return ['category_name'=>textField($data,'category_name',80)];
    if ($resource === 'customers') {
        $email = textField($data, 'email', 150);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(422, ['error' => 'Enter a valid customer email address.']);
        return ['full_name'=>textField($data,'full_name',120),'email'=>$email,'phone'=>textField($data,'phone',25,true)];
    }
    respond(404, ['error' => 'Unknown resource.']);
}

function listOrders(PDO $pdo, ?string $id = null): array
{
    $sql = 'SELECT o.order_id,o.customer_id,c.full_name AS customer_name,o.order_date,o.status,COALESCE(SUM(oi.quantity*oi.unit_price),0) AS total,COUNT(oi.book_id) AS item_count FROM orders o JOIN customers c ON c.customer_id=o.customer_id LEFT JOIN order_items oi ON oi.order_id=o.order_id';
    if ($id !== null) $sql .= ' WHERE o.order_id=?';
    $sql .= ' GROUP BY o.order_id ORDER BY o.order_date DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($id === null ? [] : [$id]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['total'] = (float)$row['total'];
        $row['items'] = $pdo->prepare('SELECT oi.book_id,b.title,oi.quantity,oi.unit_price FROM order_items oi JOIN books b ON b.book_id=oi.book_id WHERE oi.order_id=?');
        $row['items']->execute([$row['order_id']]);
        $row['items'] = $row['items']->fetchAll();
    }
    return $rows;
}

function newOrder(PDO $pdo, array $data): array
{
    $customerId = intField($data, 'customer_id', 1);
    $items = $data['items'] ?? null;
    if (!is_array($items) || count($items) === 0) respond(422, ['error' => 'Add at least one book to the order.']);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO orders (customer_id) VALUES (?)')->execute([$customerId]);
        $orderId = (int)$pdo->lastInsertId();
        foreach ($items as $item) {
            if (!is_array($item)) throw new InvalidArgumentException('Invalid order item.');
            $bookId = intField($item, 'book_id', 1);
            $quantity = intField($item, 'quantity', 1, 999);
            $bookStmt = $pdo->prepare('SELECT price,stock_qty FROM books WHERE book_id=? FOR UPDATE');
            $bookStmt->execute([$bookId]);
            $book = $bookStmt->fetch();
            if (!$book) throw new InvalidArgumentException('A selected book does not exist.');
            if ((int)$book['stock_qty'] < $quantity) throw new InvalidArgumentException('There is not enough stock for one of the selected books.');
            $pdo->prepare('INSERT INTO order_items (order_id,book_id,quantity,unit_price) VALUES (?,?,?,?)')->execute([$orderId,$bookId,$quantity,$book['price']]);
            $pdo->prepare('UPDATE books SET stock_qty=stock_qty-? WHERE book_id=?')->execute([$quantity,$bookId]);
        }
        $pdo->commit();
        return listOrders($pdo, (string)$orderId)[0];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $error;
    }
}

try {
    $pdo = database();
    $resource = (string)($_GET['resource'] ?? '');
    $id = isset($_GET['id']) ? (string)$_GET['id'] : '';
    $method = $_SERVER['REQUEST_METHOD'];
    if ($resource === 'orders') {
        if ($method === 'GET') {
            $orders = listOrders($pdo, $id !== '' ? $id : null);
            if ($id !== '' && !$orders) respond(404, ['error'=>'Order not found.']);
            respond(200, ['data'=>$id !== '' ? $orders[0] : $orders]);
        }
        if ($method === 'POST') respond(201, ['message'=>'Order created.','data'=>newOrder($pdo,inputData())]);
        if ($method === 'PUT' || $method === 'PATCH') {
            if ($id === '') respond(400,['error'=>'An order id is required.']);
            $data = inputData();
            $status = textField($data,'status',12);
            if (!in_array($status,['Pending','Processing','Shipped','Completed','Cancelled'],true)) respond(422,['error'=>'Choose a valid order status.']);
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT status FROM orders WHERE order_id=? FOR UPDATE'); $stmt->execute([$id]); $old = $stmt->fetch();
            if (!$old) { $pdo->rollBack(); respond(404,['error'=>'Order not found.']); }
            if ($old['status']==='Cancelled' && $status!=='Cancelled') { $pdo->rollBack(); respond(409,['error'=>'A cancelled order cannot be reopened.']); }
            if ($old['status']!=='Cancelled' && $status==='Cancelled') {
                $items = $pdo->prepare('SELECT book_id,quantity FROM order_items WHERE order_id=?'); $items->execute([$id]);
                foreach ($items->fetchAll() as $item) $pdo->prepare('UPDATE books SET stock_qty=stock_qty+? WHERE book_id=?')->execute([$item['quantity'],$item['book_id']]);
            }
            $pdo->prepare('UPDATE orders SET status=? WHERE order_id=?')->execute([$status,$id]); $pdo->commit();
            respond(200,['message'=>'Order status updated.','data'=>listOrders($pdo,$id)[0]]);
        }
        if ($method === 'DELETE') {
            if ($id === '') respond(400,['error'=>'An order id is required.']);
            $pdo->beginTransaction();
            $stmt=$pdo->prepare('SELECT status FROM orders WHERE order_id=? FOR UPDATE');$stmt->execute([$id]);$order=$stmt->fetch();
            if (!$order) { $pdo->rollBack(); respond(404,['error'=>'Order not found.']); }
            if ($order['status']!=='Cancelled') {
                $stmt=$pdo->prepare('SELECT book_id,quantity FROM order_items WHERE order_id=?');$stmt->execute([$id]);
                foreach($stmt->fetchAll() as $item) $pdo->prepare('UPDATE books SET stock_qty=stock_qty+? WHERE book_id=?')->execute([$item['quantity'],$item['book_id']]);
            }
            $pdo->prepare('DELETE FROM orders WHERE order_id=?')->execute([$id]);$pdo->commit();
            respond(200,['message'=>'Order deleted and stock restored.']);
        }
        respond(405,['error'=>'Method not allowed.']);
    }

    $defs = definitions();
    if (!isset($defs[$resource])) respond(404,['error'=>'Unknown resource.']);
    $def=$defs[$resource];
    if ($method==='GET') {
        $sql=$def['select']; if ($id!=='') $sql.=' WHERE '.$def['id'].'=?';
        $sql.=' ORDER BY '.$def['id'].' DESC';
        $stmt=$pdo->prepare($sql);$stmt->execute($id!==''?[$id]:[]);$records=$stmt->fetchAll();
        if ($id!=='' && !$records) respond(404,['error'=>'Record not found.']);
        respond(200,['data'=>$id!==''?$records[0]:$records]);
    }
    if ($method==='POST') {
        $record=cleanRecord($resource,inputData());$columns=array_keys($record);$marks=array_fill(0,count($columns),'?');
        $sql='INSERT INTO '.$def['table'].' ('.implode(',',$columns).') VALUES ('.implode(',',$marks).')';
        $pdo->prepare($sql)->execute(array_values($record));$record[$def['id']]=(int)$pdo->lastInsertId();
        respond(201,['message'=>'Record created.','data'=>$record]);
    }
    if ($method==='PUT'||$method==='PATCH') {
        if ($id==='') respond(400,['error'=>'An id is required for updates.']);
        $record=cleanRecord($resource,inputData());$sets=[];
        foreach(array_keys($record) as $column)$sets[]="$column=:$column";
        $record[$def['id']]=$id;
        $stmt=$pdo->prepare('UPDATE '.$def['table'].' SET '.implode(',',$sets).' WHERE '.$def['id'].'=:'.$def['id']);$stmt->execute($record);
        $check=$pdo->prepare('SELECT '.$def['id'].' FROM '.$def['table'].' WHERE '.$def['id'].'=?');$check->execute([$id]);
        if(!$check->fetch())respond(404,['error'=>'Record not found.']);
        respond(200,['message'=>'Record updated.','data'=>$record]);
    }
    if ($method==='DELETE') {
        if ($id==='') respond(400,['error'=>'An id is required for deletion.']);
        $stmt=$pdo->prepare('DELETE FROM '.$def['table'].' WHERE '.$def['id'].'=?');$stmt->execute([$id]);
        if($stmt->rowCount()===0)respond(404,['error'=>'Record not found.']);
        respond(200,['message'=>'Record deleted.']);
    }
    respond(405,['error'=>'Method not allowed.']);
} catch (InvalidArgumentException $error) {
    respond(422,['error'=>$error->getMessage()]);
} catch (PDOException $error) {
    $code = (string)$error->getCode();
    respond($code==='23000'?409:500,['error'=>$code==='23000'?'A duplicate value or related record prevented this change.':'Database operation failed. Check the database connection and submitted values.']);
} catch (Throwable $error) {
    respond(500,['error'=>'Unexpected server error.']);
}

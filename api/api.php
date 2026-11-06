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

function textField(array $data, string $key, int $max): string
{
    $value = trim((string)($data[$key] ?? ''));
    if ($value === '' || mb_strlen($value) > $max) {
        respond(422, ['error' => "$key is required and must be at most $max characters."]);
    }
    return $value;
}

function intField(array $data, string $key, int $min = 1, int $max = 2147483647): int
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
        'comics' => [
            'table' => 'comics', 'id' => 'comic_id',
            'select' => 'SELECT c.comic_id,c.catalog_code,c.title,c.series_title,c.issue_number,c.publisher_id,p.publisher_name,c.creator_id,cr.creator_name,c.genre_id,g.genre_name,c.format,c.condition_grade,c.price,c.stock_qty FROM comics c JOIN publishers p ON p.publisher_id=c.publisher_id JOIN creators cr ON cr.creator_id=c.creator_id JOIN genres g ON g.genre_id=c.genre_id',
        ],
        'publishers' => ['table'=>'publishers','id'=>'publisher_id','select'=>'SELECT publisher_id,publisher_name FROM publishers'],
        'creators' => ['table'=>'creators','id'=>'creator_id','select'=>'SELECT creator_id,creator_name FROM creators'],
        'genres' => ['table'=>'genres','id'=>'genre_id','select'=>'SELECT genre_id,genre_name FROM genres'],
        'customers' => ['table'=>'customers','id'=>'customer_id','select'=>'SELECT customer_id,full_name,email,phone FROM customers'],
    ];
}

function cleanRecord(string $resource, array $data): array
{
    if ($resource === 'comics') {
        $price = filter_var($data['price'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0 || $price > 999999.99) {
            respond(422, ['error' => 'price must be a non-negative amount.']);
        }
        $format = textField($data, 'format', 30);
        $grade = textField($data, 'condition_grade', 2);
        if (!in_array($format, ['Single Issue','Trade Paperback','Graphic Novel','Hardcover'], true)) {
            respond(422, ['error' => 'Choose a valid comic format.']);
        }
        if (!in_array($grade, ['NM','VF','FN','VG','G'], true)) {
            respond(422, ['error' => 'Choose a valid condition grade: NM, VF, FN, VG, or G.']);
        }
        return [
            'catalog_code' => textField($data, 'catalog_code', 30),
            'title' => textField($data, 'title', 180),
            'series_title' => textField($data, 'series_title', 140),
            'issue_number' => textField($data, 'issue_number', 30),
            'publisher_id' => intField($data, 'publisher_id'),
            'creator_id' => intField($data, 'creator_id'),
            'genre_id' => intField($data, 'genre_id'),
            'format' => $format,
            'condition_grade' => $grade,
            'price' => number_format((float)$price, 2, '.', ''),
            'stock_qty' => intField($data, 'stock_qty', 0),
        ];
    }
    if ($resource === 'publishers') return ['publisher_name'=>textField($data,'publisher_name',80)];
    if ($resource === 'creators') return ['creator_name'=>textField($data,'creator_name',120)];
    if ($resource === 'genres') return ['genre_name'=>textField($data,'genre_name',80)];
    if ($resource === 'customers') {
        $email = textField($data, 'email', 150);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond(422, ['error' => 'Enter a valid customer email address.']);
        return ['full_name'=>textField($data,'full_name',120),'email'=>$email,'phone'=>substr(trim((string)($data['phone'] ?? '')),0,25) ?: null];
    }
    respond(404, ['error' => 'Unknown resource.']);
}

function listOrders(PDO $pdo, ?string $id = null): array
{
    $sql = 'SELECT o.order_id,o.customer_id,c.full_name AS customer_name,o.order_date,o.status,COALESCE(SUM(oi.quantity*oi.unit_price),0) AS total,COUNT(oi.comic_id) AS item_count FROM orders o JOIN customers c ON c.customer_id=o.customer_id LEFT JOIN order_items oi ON oi.order_id=o.order_id';
    if ($id !== null) $sql .= ' WHERE o.order_id=?';
    $sql .= ' GROUP BY o.order_id ORDER BY o.order_date DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($id === null ? [] : [$id]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['total'] = (float)$row['total'];
        $items = $pdo->prepare('SELECT oi.comic_id,co.title,co.series_title,oi.quantity,oi.unit_price FROM order_items oi JOIN comics co ON co.comic_id=oi.comic_id WHERE oi.order_id=?');
        $items->execute([$row['order_id']]);
        $row['items'] = $items->fetchAll();
    }
    return $rows;
}

function newOrder(PDO $pdo, array $data): array
{
    $customerId = intField($data, 'customer_id');
    $items = $data['items'] ?? null;
    if (!is_array($items) || count($items) === 0) respond(422, ['error' => 'Add at least one comic to the order.']);
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO orders (customer_id) VALUES (?)')->execute([$customerId]);
        $orderId = (int)$pdo->lastInsertId();
        $seen = [];
        foreach ($items as $item) {
            if (!is_array($item)) throw new InvalidArgumentException('Invalid order item.');
            $comicId = intField($item, 'comic_id');
            if (isset($seen[$comicId])) throw new InvalidArgumentException('Add each comic once and set its quantity on that line.');
            $seen[$comicId] = true;
            $quantity = intField($item, 'quantity', 1, 999);
            $stockStmt = $pdo->prepare('SELECT price,stock_qty FROM comics WHERE comic_id=? FOR UPDATE');
            $stockStmt->execute([$comicId]);
            $comic = $stockStmt->fetch();
            if (!$comic) throw new InvalidArgumentException('A selected comic does not exist.');
            if ((int)$comic['stock_qty'] < $quantity) throw new InvalidArgumentException('There is not enough stock for one of the selected comics.');
            $pdo->prepare('INSERT INTO order_items (order_id,comic_id,quantity,unit_price) VALUES (?,?,?,?)')->execute([$orderId,$comicId,$quantity,$comic['price']]);
            $pdo->prepare('UPDATE comics SET stock_qty=stock_qty-? WHERE comic_id=?')->execute([$quantity,$comicId]);
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
        if ($method === 'POST') respond(201,['message'=>'Order created.','data'=>newOrder($pdo,inputData())]);
        if ($method === 'PUT' || $method === 'PATCH') {
            if ($id === '') respond(400,['error'=>'An order id is required.']);
            $status = textField(inputData(),'status',12);
            if (!in_array($status,['Pending','Processing','Shipped','Completed','Cancelled'],true)) respond(422,['error'=>'Choose a valid order status.']);
            $pdo->beginTransaction();
            $stmt=$pdo->prepare('SELECT status FROM orders WHERE order_id=? FOR UPDATE');$stmt->execute([$id]);$old=$stmt->fetch();
            if (!$old) { $pdo->rollBack(); respond(404,['error'=>'Order not found.']); }
            if ($old['status']==='Cancelled' && $status!=='Cancelled') { $pdo->rollBack(); respond(409,['error'=>'A cancelled order cannot be reopened.']); }
            if ($old['status']!=='Cancelled' && $status==='Cancelled') {
                $items=$pdo->prepare('SELECT comic_id,quantity FROM order_items WHERE order_id=?');$items->execute([$id]);
                foreach($items->fetchAll() as $item) $pdo->prepare('UPDATE comics SET stock_qty=stock_qty+? WHERE comic_id=?')->execute([$item['quantity'],$item['comic_id']]);
            }
            $pdo->prepare('UPDATE orders SET status=? WHERE order_id=?')->execute([$status,$id]);$pdo->commit();
            respond(200,['message'=>'Order status updated.','data'=>listOrders($pdo,$id)[0]]);
        }
        if ($method === 'DELETE') {
            if ($id === '') respond(400,['error'=>'An order id is required.']);
            $pdo->beginTransaction();
            $stmt=$pdo->prepare('SELECT status FROM orders WHERE order_id=? FOR UPDATE');$stmt->execute([$id]);$order=$stmt->fetch();
            if (!$order) { $pdo->rollBack(); respond(404,['error'=>'Order not found.']); }
            if ($order['status']!=='Cancelled') {
                $stmt=$pdo->prepare('SELECT comic_id,quantity FROM order_items WHERE order_id=?');$stmt->execute([$id]);
                foreach($stmt->fetchAll() as $item) $pdo->prepare('UPDATE comics SET stock_qty=stock_qty+? WHERE comic_id=?')->execute([$item['quantity'],$item['comic_id']]);
            }
            $pdo->prepare('DELETE FROM orders WHERE order_id=?')->execute([$id]);$pdo->commit();
            respond(200,['message'=>'Order deleted and stock restored.']);
        }
        respond(405,['error'=>'Method not allowed.']);
    }

    $defs=definitions();
    if (!isset($defs[$resource])) respond(404,['error'=>'Unknown resource.']);
    $def=$defs[$resource];
    if ($method==='GET') {
        $sql=$def['select'];if($id!=='')$sql.=' WHERE '.$def['id'].'=?';$sql.=' ORDER BY '.$def['id'].' DESC';
        $stmt=$pdo->prepare($sql);$stmt->execute($id!==''?[$id]:[]);$records=$stmt->fetchAll();
        if($id!==''&&!$records)respond(404,['error'=>'Record not found.']);
        respond(200,['data'=>$id!==''?$records[0]:$records]);
    }
    if ($method==='POST') {
        $record=cleanRecord($resource,inputData());$columns=array_keys($record);
        $sql='INSERT INTO '.$def['table'].' ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($columns),'?')).')';
        $pdo->prepare($sql)->execute(array_values($record));$newId=(int)$pdo->lastInsertId();
        respond(201,['message'=>'Record created.','data'=>['id'=>$newId]+$record]);
    }
    if ($method==='PUT'||$method==='PATCH') {
        if($id==='')respond(400,['error'=>'An id is required for updates.']);
        $record=cleanRecord($resource,inputData());$sets=[];
        foreach(array_keys($record) as $column)$sets[]="$column=:$column";
        $record[$def['id']]=$id;
        $stmt=$pdo->prepare('UPDATE '.$def['table'].' SET '.implode(',',$sets).' WHERE '.$def['id'].'=:'.$def['id']);$stmt->execute($record);
        $check=$pdo->prepare('SELECT '.$def['id'].' FROM '.$def['table'].' WHERE '.$def['id'].'=?');$check->execute([$id]);
        if(!$check->fetch())respond(404,['error'=>'Record not found.']);
        respond(200,['message'=>'Record updated.','data'=>$record]);
    }
    if ($method==='DELETE') {
        if($id==='')respond(400,['error'=>'An id is required for deletion.']);
        $stmt=$pdo->prepare('DELETE FROM '.$def['table'].' WHERE '.$def['id'].'=?');$stmt->execute([$id]);
        if($stmt->rowCount()===0)respond(404,['error'=>'Record not found.']);
        respond(200,['message'=>'Record deleted.']);
    }
    respond(405,['error'=>'Method not allowed.']);
} catch (InvalidArgumentException $error) {
    respond(422,['error'=>$error->getMessage()]);
} catch (PDOException $error) {
    $code=(string)$error->getCode();
    respond($code==='23000'?409:500,['error'=>$code==='23000'?'A duplicate value or related record prevented this change.':'Database operation failed. Check the database connection and submitted values.']);
} catch (Throwable $error) {
    respond(500,['error'=>'Unexpected server error.']);
}

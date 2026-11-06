const API = 'api/api.php';
const state = { books: [], authors: [], categories: [], customers: [], orders: [] };
const $ = (selector) => document.querySelector(selector);
const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));

async function api(resource, options = {}, id = '') {
  const url = `${API}?resource=${encodeURIComponent(resource)}${id ? `&id=${encodeURIComponent(id)}` : ''}`;
  const response = await fetch(url, { headers: { 'Content-Type': 'application/json' }, ...options });
  const result = await response.json();
  if (!response.ok) throw new Error(result.error || 'The request could not be completed.');
  return result.data;
}

function money(amount) { return `$${Number(amount).toFixed(2)}`; }
function showToast(message) {
  const toast = $('#toast'); toast.textContent = message; toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2600);
}

async function loadDashboard() {
  try {
    const [books, authors, categories, customers, orders] = await Promise.all([
      api('books'), api('authors'), api('categories'), api('customers'), api('orders')
    ]);
    Object.assign(state, { books, authors, categories, customers, orders });
    renderDashboard();
  } catch (error) {
    $('#bookRows').innerHTML = `<tr><td colspan="6" class="loading-cell">${escapeHtml(error.message)} Check your database connection and refresh.</td></tr>`;
    $('#orderList').innerHTML = '<div class="loading-cell">Orders could not be loaded.</div>';
    showToast(error.message);
  }
}

function renderDashboard() {
  const lowStock = state.books.filter(book => Number(book.stock_qty) < 8).length;
  $('#totalBooks').textContent = state.books.length;
  $('#stockUnits').textContent = state.books.reduce((sum, book) => sum + Number(book.stock_qty), 0).toLocaleString();
  $('#activeOrders').textContent = state.orders.filter(order => ['Pending','Processing'].includes(order.status)).length;
  $('#lowStockCount').textContent = lowStock;
  $('#navBookCount').textContent = state.books.length;
  renderFilters(); renderBooks(); renderOrders();
}

function renderFilters() {
  const current = $('#categoryFilter').value;
  $('#categoryFilter').innerHTML = '<option value="">All categories</option>' + state.categories.map(category =>
    `<option value="${category.category_id}">${escapeHtml(category.category_name)}</option>`).join('');
  $('#categoryFilter').value = current;
}

function renderBooks() {
  const query = $('#searchInput').value.trim().toLowerCase();
  const category = $('#categoryFilter').value;
  const books = state.books.filter(book => (!category || String(book.category_id) === category) &&
    `${book.title} ${book.author_name} ${book.isbn}`.toLowerCase().includes(query));
  $('#tableCount').textContent = `${books.length} ${books.length === 1 ? 'book' : 'books'} shown`;
  if (!books.length) { $('#bookRows').innerHTML = '<tr><td colspan="6" class="loading-cell">No books match your search.</td></tr>'; return; }
  $('#bookRows').innerHTML = books.map((book, index) => `<tr>
    <td><div class="book-cell"><div class="book-cover cover-${index % 4}">${escapeHtml(book.title.slice(0,1))}</div><div><div class="book-title">${escapeHtml(book.title)}</div><div class="book-author">${escapeHtml(book.author_name)}</div></div></div></td>
    <td>${escapeHtml(book.isbn)}</td><td><span class="category-pill">${escapeHtml(book.category_name)}</span></td>
    <td class="price">${money(book.price)}</td><td><span class="stock-pill ${Number(book.stock_qty)<8?'stock-low':'stock-good'}">${Number(book.stock_qty)} ${Number(book.stock_qty)===1?'copy':'copies'}</span></td>
    <td><div class="row-actions"><button class="row-action" data-edit="${book.book_id}" aria-label="Edit ${escapeHtml(book.title)}">✎</button><button class="row-action" data-delete="${book.book_id}" aria-label="Delete ${escapeHtml(book.title)}">×</button></div></td></tr>`).join('');
}

function renderOrders() {
  if (!state.orders.length) { $('#orderList').innerHTML = '<div class="loading-cell">No orders have been placed yet.</div>'; return; }
  $('#orderList').innerHTML = state.orders.slice(0,4).map(order => {
    const items = order.items.map(item => `${Number(item.quantity)} × ${escapeHtml(item.title)}`).join('<br>');
    return `<article class="order-card"><div class="order-head"><div><span class="order-name">${escapeHtml(order.customer_name)}</span><span class="order-id">#${order.order_id}</span></div><span class="status-badge status-${escapeHtml(order.status)}">${escapeHtml(order.status)}</span></div><div class="order-lines">${items || 'No line items'}</div><div class="order-bottom"><div class="order-total">${money(order.total)}</div><select class="status-select" data-order-status="${order.order_id}" aria-label="Update order ${order.order_id} status"><option ${order.status==='Pending'?'selected':''}>Pending</option><option ${order.status==='Processing'?'selected':''}>Processing</option><option ${order.status==='Shipped'?'selected':''}>Shipped</option><option ${order.status==='Completed'?'selected':''}>Completed</option><option ${order.status==='Cancelled'?'selected':''}>Cancelled</option></select></div></article>`;
  }).join('');
}

function openBookDialog(book = null) {
  $('#bookForm').reset(); $('#formError').textContent = '';
  $('#bookId').value = book?.book_id ?? '';
  $('#dialogTitle').textContent = book ? 'Edit book details' : 'Add a book';
  $('#bookTitle').value = book?.title ?? ''; $('#bookIsbn').value = book?.isbn ?? '';
  $('#bookPublisher').value = book?.publisher ?? ''; $('#bookPrice').value = book?.price ?? '';
  $('#bookStock').value = book?.stock_qty ?? 0;
  $('#bookAuthor').innerHTML = state.authors.map(author => `<option value="${author.author_id}">${escapeHtml(author.name)}</option>`).join('');
  $('#bookCategory').innerHTML = state.categories.map(category => `<option value="${category.category_id}">${escapeHtml(category.category_name)}</option>`).join('');
  if (book) { $('#bookAuthor').value = book.author_id; $('#bookCategory').value = book.category_id; }
  $('#bookDialog').showModal();
}

$('#addBookButton').addEventListener('click', () => openBookDialog());
$('#closeDialog').addEventListener('click', () => $('#bookDialog').close());
$('#cancelDialog').addEventListener('click', () => $('#bookDialog').close());
$('#searchInput').addEventListener('input', renderBooks);
$('#categoryFilter').addEventListener('change', renderBooks);
$('#refreshButton').addEventListener('click', loadDashboard);
$('#bookRows').addEventListener('click', async (event) => {
  const editId = event.target.closest('[data-edit]')?.dataset.edit;
  const deleteId = event.target.closest('[data-delete]')?.dataset.delete;
  if (editId) openBookDialog(state.books.find(book => String(book.book_id) === editId));
  if (deleteId) {
    const book = state.books.find(item => String(item.book_id) === deleteId);
    if (!confirm(`Delete “${book.title}” from the catalog?`)) return;
    try { await api('books', { method: 'DELETE' }, deleteId); showToast('Book deleted.'); loadDashboard(); }
    catch (error) { showToast(error.message); }
  }
});

$('#bookForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const id = $('#bookId').value;
  const payload = { title: $('#bookTitle').value, isbn: $('#bookIsbn').value, publisher: $('#bookPublisher').value,
    author_id: Number($('#bookAuthor').value), category_id: Number($('#bookCategory').value),
    price: Number($('#bookPrice').value), stock_qty: Number($('#bookStock').value) };
  try {
    await api('books', { method: id ? 'PUT' : 'POST', body: JSON.stringify(payload) }, id);
    $('#bookDialog').close(); showToast(id ? 'Book details updated.' : 'Book added to the catalog.'); await loadDashboard();
  } catch (error) { $('#formError').textContent = error.message; }
});

$('#orderList').addEventListener('change', async (event) => {
  const id = event.target.dataset.orderStatus;
  if (!id) return;
  try { await api('orders', { method:'PUT', body:JSON.stringify({status:event.target.value}) }, id); showToast('Order status updated.'); await loadDashboard(); }
  catch (error) { showToast(error.message); await loadDashboard(); }
});

$('#exportButton').addEventListener('click', () => {
  const headings = ['Book ID','ISBN','Title','Author','Category','Publisher','Price','Stock'];
  const rows = state.books.map(book => [book.book_id,book.isbn,book.title,book.author_name,book.category_name,book.publisher,book.price,book.stock_qty]);
  const csv = [headings,...rows].map(row => row.map(value => `"${String(value ?? '').replaceAll('"','""')}"`).join(',')).join('\n');
  const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'})); link.download='book-inventory.csv'; link.click(); URL.revokeObjectURL(link.href);
});

const now = new Date();
$('#todayLabel').textContent = new Intl.DateTimeFormat('en-US',{weekday:'short',month:'short',day:'numeric',year:'numeric'}).format(now);
$('#welcomeDate').textContent = new Intl.DateTimeFormat('en-US',{weekday:'long',month:'long',day:'numeric',year:'numeric'}).format(now).toUpperCase();
loadDashboard();

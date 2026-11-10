const API = 'api/api.php';
const state = { comics: [], publishers: [], creators: [], genres: [], customers: [], orders: [] };
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
    const [comics, publishers, creators, genres, customers, orders] = await Promise.all([
      api('comics'), api('publishers'), api('creators'), api('genres'), api('customers'), api('orders')
    ]);
    Object.assign(state, { comics, publishers, creators, genres, customers, orders });
    renderDashboard();
  } catch (error) {
    $('#comicRows').innerHTML = `<tr><td colspan="7" class="loading-cell">${escapeHtml(error.message)} Check your database connection and refresh.</td></tr>`;
    $('#orderList').innerHTML = '<div class="loading-cell">Orders could not be loaded.</div>';
    showToast(error.message);
  }
}

function renderDashboard() {
  const lowStock = state.comics.filter(comic => Number(comic.stock_qty) < 8).length;
  $('#totalComics').textContent = state.comics.length;
  $('#stockUnits').textContent = state.comics.reduce((sum, comic) => sum + Number(comic.stock_qty), 0).toLocaleString();
  $('#activeOrders').textContent = state.orders.filter(order => ['Pending','Processing'].includes(order.status)).length;
  $('#lowStockCount').textContent = lowStock;
  $('#navComicCount').textContent = state.comics.length;
  renderPublisherFilter(); renderComics(); renderOrders(); renderOrderTable(); renderCatalogEntities();
}

function renderPublisherFilter() {
  const current = $('#publisherFilter').value;
  $('#publisherFilter').innerHTML = '<option value="">All publishers</option>' + state.publishers.map(publisher =>
    `<option value="${publisher.publisher_id}">${escapeHtml(publisher.publisher_name)}</option>`).join('');
  $('#publisherFilter').value = current;
}

function renderComics() {
  const query = $('#searchInput').value.trim().toLowerCase();
  const publisher = $('#publisherFilter').value;
  const comics = state.comics.filter(comic =>
    (!publisher || String(comic.publisher_id) === publisher) &&
    `${comic.title} ${comic.series_title} ${comic.issue_number} ${comic.creator_name} ${comic.catalog_code}`.toLowerCase().includes(query));
  $('#tableCount').textContent = `${comics.length} ${comics.length === 1 ? 'edition' : 'editions'} shown`;
  if (!comics.length) { $('#comicRows').innerHTML = '<tr><td colspan="7" class="loading-cell">No comics match your search.</td></tr>'; return; }
  $('#comicRows').innerHTML = comics.map((comic, index) => `<tr>
    <td><div class="comic-cell"><div class="comic-cover cover-${index % 4}">${escapeHtml(comic.publisher_name.startsWith('Marvel') ? 'M' : 'D')}</div><div><div class="comic-title">${escapeHtml(comic.title)}</div><div class="comic-subtitle">${escapeHtml(comic.series_title)} · ${escapeHtml(comic.creator_name)}</div></div></div></td>
    <td><span class="publisher-pill ${comic.publisher_name.startsWith('Marvel') ? 'marvel' : 'dc'}">${escapeHtml(comic.publisher_name)}</span></td>
    <td><span class="issue-number">#${escapeHtml(comic.issue_number)}</span><small class="format-label">${escapeHtml(comic.format)}</small></td>
    <td><span class="grade-pill grade-${escapeHtml(comic.condition_grade)}">${escapeHtml(comic.condition_grade)}</span></td>
    <td class="price">${money(comic.price)}</td><td><span class="stock-pill ${Number(comic.stock_qty)<8?'stock-low':'stock-good'}">${Number(comic.stock_qty)} ${Number(comic.stock_qty)===1?'copy':'copies'}</span></td>
    <td><div class="row-actions"><button class="row-action" data-edit="${comic.comic_id}" aria-label="Edit ${escapeHtml(comic.title)}">✎</button><button class="row-action" data-delete="${comic.comic_id}" aria-label="Delete ${escapeHtml(comic.title)}">×</button></div></td></tr>`).join('');
}

function renderOrders() {
  if (!state.orders.length) { $('#orderList').innerHTML = '<div class="loading-cell">No orders have been placed yet.</div>'; return; }
  $('#orderList').innerHTML = state.orders.slice(0,4).map(order => {
    const items = order.items.map(item => `${Number(item.quantity)} × ${escapeHtml(item.title)}`).join('<br>');
    return `<article class="order-card"><div class="order-head"><div><span class="order-name">${escapeHtml(order.customer_name)}</span><span class="order-id">#${order.order_id}</span></div><span class="status-badge status-${escapeHtml(order.status)}">${escapeHtml(order.status)}</span></div><div class="order-lines">${items || 'No line items'}</div><div class="order-bottom"><div class="order-total">${money(order.total)}</div><select class="status-select" data-order-status="${order.order_id}" aria-label="Update order ${order.order_id} status">${orderStatusOptions(order.status)}</select></div></article>`;
  }).join('');
}

function orderStatusOptions(selected) {
  return ['Pending','Processing','Shipped','Completed','Cancelled'].map(status =>
    `<option ${status === selected ? 'selected' : ''} ${selected === 'Cancelled' && status !== 'Cancelled' ? 'disabled' : ''}>${status}</option>`).join('');
}

function renderOrderTable() {
  if (!state.orders.length) { $('#allOrderRows').innerHTML = '<tr><td colspan="7" class="loading-cell">No orders yet. Create the first customer order.</td></tr>'; return; }
  $('#allOrderRows').innerHTML = state.orders.map(order => {
    const items = order.items.map(item => `${Number(item.quantity)} × ${escapeHtml(item.title)}`).join('<br>');
    const date = new Date(order.order_date.replace(' ', 'T')).toLocaleDateString('en-US');
    return `<tr><td>#${order.order_id}</td><td>${escapeHtml(order.customer_name)}</td><td>${date}</td><td class="order-table-items">${items}</td><td class="price">${money(order.total)}</td><td><select class="status-select" data-order-status="${order.order_id}" aria-label="Update order ${order.order_id} status">${orderStatusOptions(order.status)}</select></td><td><button class="row-action delete-order" data-order-delete="${order.order_id}" aria-label="Delete order ${order.order_id}">Delete</button></td></tr>`;
  }).join('');
}

function renderCatalogEntities() {
  $('#publisherRows').innerHTML = state.publishers.map(publisher => {
    const count = state.comics.filter(comic => String(comic.publisher_id) === String(publisher.publisher_id)).length;
    return `<tr><td>${escapeHtml(publisher.publisher_name)}</td><td>${count}</td><td><div class="row-actions"><button class="row-action" data-edit-entity="publishers" data-id="${publisher.publisher_id}">Edit</button><button class="row-action" data-delete-entity="publishers" data-id="${publisher.publisher_id}">Delete</button></div></td></tr>`;
  }).join('') || '<tr><td colspan="3" class="loading-cell">No publishers yet. Add one to use it in the comic form.</td></tr>';
  $('#creatorRows').innerHTML = state.creators.map(creator => {
    const count = state.comics.filter(comic => String(comic.creator_id) === String(creator.creator_id)).length;
    return `<tr><td>${escapeHtml(creator.creator_name)}</td><td>${count}</td><td><div class="row-actions"><button class="row-action" data-edit-entity="creators" data-id="${creator.creator_id}">Edit</button><button class="row-action" data-delete-entity="creators" data-id="${creator.creator_id}">Delete</button></div></td></tr>`;
  }).join('') || '<tr><td colspan="3" class="loading-cell">No creators yet. Add one to use it in the comic form.</td></tr>';
}

function openEntityDialog(resource, record = null) {
  const label = resource === 'publishers' ? 'Publisher' : 'Creator';
  $('#entityForm').reset(); $('#entityError').textContent = '';
  $('#entityType').value = resource;
  $('#entityName').maxLength = resource === 'publishers' ? 80 : 120;
  $('#entityId').value = record ? record[resource === 'publishers' ? 'publisher_id' : 'creator_id'] : '';
  $('#entityTypeLabel').textContent = 'CATALOG SETTINGS';
  $('#entityDialogTitle').textContent = `${record ? 'Edit' : 'Add'} ${label.toLowerCase()}`;
  $('#entityNameLabel').firstChild.textContent = `${label} name`;
  $('#entityName').value = record ? record[resource === 'publishers' ? 'publisher_name' : 'creator_name'] : '';
  $('#entityDialog').showModal();
}

function addOrderItemRow(comicId = '') {
  const row = document.createElement('div'); row.className = 'order-item-entry';
  const choices = state.comics.filter(comic => Number(comic.stock_qty) > 0);
  row.innerHTML = `<select data-order-comic required aria-label="Comic edition"><option value="">Choose a comic</option>${choices.map(comic => `<option value="${comic.comic_id}" ${String(comic.comic_id) === String(comicId) ? 'selected' : ''}>${escapeHtml(comic.title)} · ${Number(comic.stock_qty)} in stock · ${money(comic.price)}</option>`).join('')}</select><input data-order-quantity type="number" min="1" value="1" required aria-label="Quantity"><button class="row-action" type="button" data-remove-order-item aria-label="Remove line">×</button>`;
  $('#orderItemInputs').append(row);
}

function openOrderDialog() {
  if (!state.customers.length) { showToast('Add a customer before creating an order.'); return; }
  if (!state.comics.some(comic => Number(comic.stock_qty) > 0)) { showToast('Add comic stock before creating an order.'); return; }
  $('#orderForm').reset(); $('#orderError').textContent = ''; $('#orderItemInputs').innerHTML = '';
  setOptions('#orderCustomer', state.customers, 'customer_id', 'full_name');
  addOrderItemRow(); $('#orderDialog').showModal();
}

function setOptions(selector, rows, idKey, labelKey, currentValue) {
  $(selector).innerHTML = rows.map(row => `<option value="${row[idKey]}">${escapeHtml(row[labelKey])}</option>`).join('');
  if (currentValue !== undefined && currentValue !== null) $(selector).value = String(currentValue);
}

function openComicDialog(comic = null) {
  $('#comicForm').reset(); $('#formError').textContent = '';
  $('#comicId').value = comic?.comic_id ?? '';
  $('#dialogTitle').textContent = comic ? 'Edit comic edition' : 'Add a comic';
  $('#comicTitle').value = comic?.title ?? '';
  $('#seriesTitle').value = comic?.series_title ?? '';
  $('#issueNumber').value = comic?.issue_number ?? '';
  $('#catalogCode').value = comic?.catalog_code ?? '';
  $('#comicPrice').value = comic?.price ?? '';
  $('#comicStock').value = comic?.stock_qty ?? 0;
  setOptions('#comicPublisher',state.publishers,'publisher_id','publisher_name',comic?.publisher_id ?? state.publishers[0]?.publisher_id);
  setOptions('#comicCreator',state.creators,'creator_id','creator_name',comic?.creator_id ?? state.creators[0]?.creator_id);
  setOptions('#comicGenre',state.genres,'genre_id','genre_name',comic?.genre_id ?? state.genres[0]?.genre_id);
  $('#comicFormat').value = comic?.format ?? 'Single Issue';
  $('#conditionGrade').value = comic?.condition_grade ?? 'VF';
  $('#comicDialog').showModal();
}

$('#addComicButton').addEventListener('click', () => openComicDialog());
$('#closeDialog').addEventListener('click', () => $('#comicDialog').close());
$('#cancelDialog').addEventListener('click', () => $('#comicDialog').close());
$('#searchInput').addEventListener('input', renderComics);
$('#publisherFilter').addEventListener('change', renderComics);
$('#refreshButton').addEventListener('click', loadDashboard);

document.querySelectorAll('.nav-item').forEach(link => link.addEventListener('click', () => {
  document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
  link.classList.add('active');
}));

$('#comicRows').addEventListener('click', async (event) => {
  const editId = event.target.closest('[data-edit]')?.dataset.edit;
  const deleteId = event.target.closest('[data-delete]')?.dataset.delete;
  if (editId) openComicDialog(state.comics.find(comic => String(comic.comic_id) === editId));
  if (deleteId) {
    const comic = state.comics.find(item => String(item.comic_id) === deleteId);
    if (!confirm(`Delete “${comic.title}” from the comic catalog?`)) return;
    try { await api('comics', { method: 'DELETE' }, deleteId); showToast('Comic removed from the catalog.'); await loadDashboard(); }
    catch (error) { showToast(error.message); }
  }
});

$('#comicForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const id = $('#comicId').value;
  const payload = {
    title: $('#comicTitle').value, series_title: $('#seriesTitle').value,
    issue_number: $('#issueNumber').value, catalog_code: $('#catalogCode').value,
    publisher_id: Number($('#comicPublisher').value), creator_id: Number($('#comicCreator').value),
    genre_id: Number($('#comicGenre').value), format: $('#comicFormat').value,
    condition_grade: $('#conditionGrade').value, price: Number($('#comicPrice').value),
    stock_qty: Number($('#comicStock').value)
  };
  try {
    await api('comics', { method: id ? 'PUT' : 'POST', body: JSON.stringify(payload) }, id);
    $('#comicDialog').close(); showToast(id ? 'Comic details updated.' : 'Comic added to the catalog.'); await loadDashboard();
  } catch (error) { $('#formError').textContent = error.message; }
});

document.addEventListener('change', async (event) => {
  const id = event.target.dataset.orderStatus;
  if (!id) return;
  try {
    await api('orders', { method:'PUT', body:JSON.stringify({status:event.target.value}) }, id);
    showToast(event.target.value === 'Cancelled' ? 'Order cancelled and stock returned.' : 'Order status updated.'); await loadDashboard();
  }
  catch (error) { showToast(error.message); await loadDashboard(); }
});

document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => $(`#${button.dataset.closeDialog}`).close()));

document.addEventListener('click', async event => {
  const addEntity = event.target.closest('[data-add-entity]')?.dataset.addEntity;
  const editButton = event.target.closest('[data-edit-entity]');
  const deleteButton = event.target.closest('[data-delete-entity]');
  if (addEntity) openEntityDialog(addEntity);
  if (editButton) {
    const resource = editButton.dataset.editEntity, id = editButton.dataset.id;
    const key = resource === 'publishers' ? 'publisher_id' : 'creator_id';
    openEntityDialog(resource, state[resource].find(record => String(record[key]) === id));
  }
  if (deleteButton) {
    const resource = deleteButton.dataset.deleteEntity, id = deleteButton.dataset.id;
    const key = resource === 'publishers' ? 'publisher_id' : 'creator_id';
    const labelKey = resource === 'publishers' ? 'publisher_name' : 'creator_name';
    const record = state[resource].find(item => String(item[key]) === id);
    if (!record || !confirm(`Delete ${record[labelKey]}? It must not be assigned to any comic.`)) return;
    try { await api(resource, {method:'DELETE'}, id); showToast(`${resource === 'publishers' ? 'Publisher' : 'Creator'} deleted.`); await loadDashboard(); }
    catch (error) { showToast(error.message); }
  }
  const orderId = event.target.closest('[data-order-delete]')?.dataset.orderDelete;
  if (orderId) {
    if (!confirm(`Delete order #${orderId}? Any reserved copies will be returned to inventory.`)) return;
    try { await api('orders', {method:'DELETE'}, orderId); showToast('Order deleted and stock returned.'); await loadDashboard(); }
    catch (error) { showToast(error.message); }
  }
  if (event.target.closest('#addOrderButton')) openOrderDialog();
  if (event.target.closest('#addOrderItemButton')) addOrderItemRow();
  if (event.target.closest('[data-remove-order-item]')) {
    const rows = document.querySelectorAll('.order-item-entry');
    if (rows.length > 1) event.target.closest('.order-item-entry').remove();
    else showToast('An order needs at least one comic.');
  }
});

$('#entityForm').addEventListener('submit', async event => {
  event.preventDefault();
  const resource = $('#entityType').value, id = $('#entityId').value;
  const field = resource === 'publishers' ? 'publisher_name' : 'creator_name';
  try {
    await api(resource, {method:id ? 'PUT' : 'POST',body:JSON.stringify({[field]:$('#entityName').value})}, id);
    $('#entityDialog').close(); showToast(`${resource === 'publishers' ? 'Publisher' : 'Creator'} ${id ? 'updated' : 'added'}.`); await loadDashboard();
  } catch (error) { $('#entityError').textContent = error.message; }
});

$('#orderForm').addEventListener('submit', async event => {
  event.preventDefault();
  const items = [...document.querySelectorAll('.order-item-entry')].map(row => ({comic_id:Number(row.querySelector('[data-order-comic]').value),quantity:Number(row.querySelector('[data-order-quantity]').value)}));
  if (items.some(item => !item.comic_id || !Number.isInteger(item.quantity) || item.quantity < 1)) { $('#orderError').textContent = 'Choose a comic and a quantity for every order line.'; return; }
  if (new Set(items.map(item => item.comic_id)).size !== items.length) { $('#orderError').textContent = 'Choose each comic once. Increase its quantity on that line instead.'; return; }
  try {
    await api('orders',{method:'POST',body:JSON.stringify({customer_id:Number($('#orderCustomer').value),items})});
    $('#orderDialog').close(); showToast('Order placed and comic stock updated.'); await loadDashboard();
  } catch (error) { $('#orderError').textContent = error.message; }
});

$('#exportButton').addEventListener('click', () => {
  const headings = ['Comic ID','Catalog code','Title','Series','Issue or range','Publisher','Creator','Genre','Format','Condition','Price','Stock'];
  const rows = state.comics.map(comic => [comic.comic_id,comic.catalog_code,comic.title,comic.series_title,comic.issue_number,comic.publisher_name,comic.creator_name,comic.genre_name,comic.format,comic.condition_grade,comic.price,comic.stock_qty]);
  const csv = [headings,...rows].map(row => row.map(value => `"${String(value ?? '').replaceAll('"','""')}"`).join(',')).join('\n');
  const link = document.createElement('a'); link.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'})); link.download='comic-inventory.csv'; link.click(); URL.revokeObjectURL(link.href);
});

const now = new Date();
$('#todayLabel').textContent = new Intl.DateTimeFormat('en-US',{weekday:'short',month:'short',day:'numeric',year:'numeric'}).format(now);
$('#welcomeDate').textContent = new Intl.DateTimeFormat('en-US',{weekday:'long',month:'long',day:'numeric',year:'numeric'}).format(now).toUpperCase();
loadDashboard();

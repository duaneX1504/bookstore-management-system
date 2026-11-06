<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Page &amp; Pine | Bookstore Manager</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <a class="brand" href="#overview" aria-label="Page and Pine home"><span class="brand-mark">P</span><span>page &amp; pine<small>BOOKSTORE MANAGER</small></span></a>
      <div class="nav-label">WORKSPACE</div>
      <nav class="nav-menu">
        <a class="nav-item active" href="#overview"><span class="nav-icon">▦</span> Overview</a>
        <a class="nav-item" href="#inventory"><span class="nav-icon">▤</span> Book inventory <span class="nav-count" id="navBookCount">0</span></a>
        <a class="nav-item" href="#orders"><span class="nav-icon">▧</span> Orders</a>
        <a class="nav-item" href="#inventory"><span class="nav-icon">◫</span> Catalog</a>
      </nav>
      <div class="sidebar-note"><span class="note-icon">✦</span><div><strong>Keep the shelves ready</strong><p>Check low stock before your next order arrives.</p></div></div>
      <div class="profile"><div class="avatar">DH</div><div><strong>Store Admin</strong><small>Bookshop team</small></div><span class="profile-dots">•••</span></div>
    </aside>

    <main class="main-content" id="overview">
      <header class="topbar"><div class="breadcrumbs">Workspace <span>/</span> Overview</div><div class="top-actions"><span class="today" id="todayLabel"></span><button class="icon-button" aria-label="Notifications">♧<i></i></button><div class="avatar small-avatar">DH</div></div></header>
      <section class="welcome-row"><div><div class="eyebrow" id="welcomeDate"></div><h1>Good afternoon, Store Admin <span>✦</span></h1><p>Here’s what’s happening at your bookstore today.</p></div><button class="primary-button" id="addBookButton"><span>＋</span> Add a book</button></section>

      <section class="metrics" aria-label="Store summary">
        <article class="metric-card"><div class="metric-top"><span class="metric-icon violet">▤</span><span class="metric-change">Catalog</span></div><div class="metric-value" id="totalBooks">—</div><div class="metric-label">Books in catalog</div><div class="metric-foot">Across all categories</div></article>
        <article class="metric-card"><div class="metric-top"><span class="metric-icon green">▣</span><span class="metric-change positive">In stock</span></div><div class="metric-value" id="stockUnits">—</div><div class="metric-label">Units on hand</div><div class="metric-foot">Available to sell</div></article>
        <article class="metric-card"><div class="metric-top"><span class="metric-icon orange">▧</span><span class="metric-change">Orders</span></div><div class="metric-value" id="activeOrders">—</div><div class="metric-label">Open orders</div><div class="metric-foot">Pending or processing</div></article>
        <article class="metric-card"><div class="metric-top"><span class="metric-icon red">!</span><span class="metric-change alert">Needs attention</span></div><div class="metric-value" id="lowStockCount">—</div><div class="metric-label">Low stock titles</div><div class="metric-foot">Fewer than 8 copies</div></article>
      </section>

      <section class="content-grid">
        <article class="panel inventory-panel" id="inventory">
          <div class="panel-heading"><div><div class="section-kicker">YOUR COLLECTION</div><h2>Book inventory</h2><p>Manage titles, pricing, and stock levels.</p></div><button class="text-button" id="exportButton">Export list <span>↗</span></button></div>
          <div class="toolbar"><label class="search-box"><span>⌕</span><input id="searchInput" type="search" placeholder="Search titles, authors, ISBN..."></label><label class="filter-wrap"><span>Category</span><select id="categoryFilter"><option value="">All categories</option></select></label><button class="filter-button" id="refreshButton" aria-label="Refresh inventory">↻</button></div>
          <div class="table-wrap"><table><thead><tr><th>BOOK</th><th>ISBN</th><th>CATEGORY</th><th>PRICE</th><th>STOCK</th><th></th></tr></thead><tbody id="bookRows"><tr><td colspan="6" class="loading-cell">Loading your collection…</td></tr></tbody></table></div>
          <div class="table-footer"><span id="tableCount">Loading books</span><span class="table-footer-right">Stock levels update when orders are placed</span></div>
        </article>
        <aside class="panel orders-panel" id="orders">
          <div class="panel-heading compact"><div><div class="section-kicker">SALES</div><h2>Recent orders</h2></div><a class="small-link" href="#orders">View all <span>→</span></a></div>
          <div id="orderList" class="order-list"><div class="loading-cell">Loading orders…</div></div>
          <div class="order-tip"><span>✦</span><p>New orders automatically reduce the available stock for each book.</p></div>
        </aside>
      </section>
      <footer class="page-footer"><span>Page &amp; Pine Bookstore Manager</span><span>Inventory made simple</span></footer>
    </main>
  </div>

  <dialog id="bookDialog" class="book-dialog">
    <form id="bookForm" method="dialog">
      <div class="dialog-heading"><div><div class="section-kicker">CATALOG</div><h2 id="dialogTitle">Add a book</h2></div><button class="close-button" type="button" id="closeDialog" aria-label="Close">×</button></div>
      <input type="hidden" id="bookId">
      <label>Book title<input id="bookTitle" maxlength="180" required></label>
      <div class="form-row"><label>ISBN<input id="bookIsbn" maxlength="20" required></label><label>Publisher<input id="bookPublisher" maxlength="120"></label></div>
      <div class="form-row"><label>Author<select id="bookAuthor" required></select></label><label>Category<select id="bookCategory" required></select></label></div>
      <div class="form-row"><label>Price ($)<input id="bookPrice" type="number" min="0" step="0.01" required></label><label>Stock quantity<input id="bookStock" type="number" min="0" step="1" required></label></div>
      <div class="form-error" id="formError" role="alert"></div>
      <div class="dialog-actions"><button class="secondary-button" type="button" id="cancelDialog">Cancel</button><button class="primary-button" type="submit" id="saveBookButton">Save book</button></div>
    </form>
  </dialog>
  <div class="toast" id="toast" role="status"></div>
  <script src="assets/app.js"></script>
</body>
</html>

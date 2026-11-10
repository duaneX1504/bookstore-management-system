<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#111827">
  <title>Panel &amp; Page | Comic Shop Manager</title>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar">
      <a class="brand" href="#overview" aria-label="Panel and Page home"><span class="brand-mark">P</span><span>panel &amp; page<small>COMIC SHOP MANAGER</small></span></a>
      <div class="nav-label">SHOP WORKSPACE</div>
      <nav class="nav-menu">
        <a class="nav-item active" href="#overview" aria-label="Overview"><span class="nav-icon">▦</span> Overview</a>
        <a class="nav-item" href="#inventory" aria-label="Comic inventory"><span class="nav-icon">▤</span> Comic inventory <span class="nav-count" id="navComicCount">0</span></a>
        <a class="nav-item" href="#orders" aria-label="Orders"><span class="nav-icon">▧</span> Orders</a>
        <a class="nav-item" href="#publishers" aria-label="Publishers"><span class="nav-icon">◫</span> Publishers</a>
        <a class="nav-item" href="#creators" aria-label="Creators"><span class="nav-icon">✎</span> Creators</a>
      </nav>
      <div class="sidebar-note"><span class="note-icon">✦</span><div><strong>Keep the pull list ready</strong><p>Check low stock before the next release day.</p></div></div>
      <div class="profile"><div class="avatar">DH</div><div><strong>Store Admin</strong><small>Comic shop team</small></div><span class="profile-dots">•••</span></div>
    </aside>

    <main class="main-content" id="overview">
      <header class="topbar"><div class="breadcrumbs">Shop workspace <span>/</span> Overview</div><div class="top-actions"><span class="today" id="todayLabel"></span><button class="icon-button" aria-label="Notifications">♧<i></i></button><div class="avatar small-avatar">DH</div></div></header>
      <section class="welcome-row"><div><div class="eyebrow" id="welcomeDate"></div><h1>Welcome back, Store Admin <span>✦</span></h1><p>Here’s what’s happening at your comic shop today.</p></div><button class="primary-button" id="addComicButton"><span>＋</span> Add a comic</button></section>

      <section class="metrics" aria-label="Comic shop summary">
        <article class="metric-card"><div class="metric-top"><span class="metric-icon violet">▤</span><span class="metric-change">Catalog</span></div><div class="metric-value" id="totalComics">—</div><div class="metric-label">Comic editions</div><div class="metric-foot">Across Marvel and DC</div></article>
        <article class="metric-card"><div class="metric-top"><span class="metric-icon green">▣</span><span class="metric-change positive">On the shelf</span></div><div class="metric-value" id="stockUnits">—</div><div class="metric-label">Copies in stock</div><div class="metric-foot">Available to sell</div></article>
        <article class="metric-card"><div class="metric-top"><span class="metric-icon orange">▧</span><span class="metric-change">Orders</span></div><div class="metric-value" id="activeOrders">—</div><div class="metric-label">Open orders</div><div class="metric-foot">Pending or processing</div></article>
        <article class="metric-card"><div class="metric-top"><span class="metric-icon red">!</span><span class="metric-change alert">Pull list check</span></div><div class="metric-value" id="lowStockCount">—</div><div class="metric-label">Low-stock editions</div><div class="metric-foot">Fewer than 8 copies</div></article>
      </section>

      <section class="content-grid">
        <article class="panel inventory-panel" id="inventory">
          <div class="panel-heading"><div><div class="section-kicker">THE COMIC WALL</div><h2>Comic inventory</h2><p>Track series, issue numbers, condition, and quantity.</p></div><button class="text-button" id="exportButton">Export list <span>↗</span></button></div>
          <div class="toolbar"><label class="search-box"><span>⌕</span><input id="searchInput" type="search" placeholder="Search comics, creators, series..."></label><label class="filter-wrap"><span>Publisher</span><select id="publisherFilter"><option value="">All publishers</option></select></label><button class="filter-button" id="refreshButton" aria-label="Refresh inventory">↻</button></div>
          <div class="table-wrap"><table><thead><tr><th>COMIC / SERIES</th><th>PUBLISHER</th><th>ISSUE / FORMAT</th><th>GRADE</th><th>PRICE</th><th>STOCK</th><th></th></tr></thead><tbody id="comicRows"><tr><td colspan="7" class="loading-cell">Loading the comic wall…</td></tr></tbody></table></div>
          <div class="table-footer"><span id="tableCount">Loading comics</span><span class="table-footer-right">Order activity updates available stock</span></div>
        </article>
        <aside class="panel orders-panel" id="recentOrdersPanel">
          <div class="panel-heading compact"><div><div class="section-kicker">PULL DESK</div><h2>Recent orders</h2></div><a class="small-link" href="#orders">View all <span>→</span></a></div>
          <div id="orderList" class="order-list"><div class="loading-cell">Loading orders…</div></div>
          <div class="order-tip"><span>✦</span><p>New orders reserve the selected comic copies. Cancelling restores the stock.</p></div>
        </aside>
      </section>

      <section class="panel management-panel" id="orders">
        <div class="panel-heading"><div><div class="section-kicker">SALES</div><h2>Customer orders</h2><p>Create orders, update their status, cancel them, or remove them.</p></div><button class="primary-button" id="addOrderButton"><span>＋</span> Create order</button></div>
        <div class="table-wrap management-table"><table><thead><tr><th>ORDER</th><th>CUSTOMER</th><th>DATE</th><th>COMICS</th><th>TOTAL</th><th>STATUS</th><th></th></tr></thead><tbody id="allOrderRows"><tr><td colspan="7" class="loading-cell">Loading orders…</td></tr></tbody></table></div>
        <div class="management-help">Cancelling an order returns its copies to inventory. Order line items and sale prices are kept together as the order record.</div>
      </section>

      <section class="panel management-panel" id="publishers">
        <div class="panel-heading"><div><div class="section-kicker">CATALOG SETTINGS</div><h2>Publishers</h2><p>Add or rename publishers. Publishers used by comics cannot be deleted.</p></div><button class="primary-button" data-add-entity="publishers"><span>＋</span> Add publisher</button></div>
        <div class="table-wrap management-table"><table><thead><tr><th>PUBLISHER</th><th>COMICS</th><th></th></tr></thead><tbody id="publisherRows"><tr><td colspan="3" class="loading-cell">Loading publishers…</td></tr></tbody></table></div>
      </section>

      <section class="panel management-panel" id="creators">
        <div class="panel-heading"><div><div class="section-kicker">CATALOG SETTINGS</div><h2>Creators</h2><p>Add or rename creators. Creators credited on comics cannot be deleted.</p></div><button class="primary-button" data-add-entity="creators"><span>＋</span> Add creator</button></div>
        <div class="table-wrap management-table"><table><thead><tr><th>CREATOR</th><th>COMICS</th><th></th></tr></thead><tbody id="creatorRows"><tr><td colspan="3" class="loading-cell">Loading creators…</td></tr></tbody></table></div>
      </section>
      <footer class="page-footer"><span>Panel &amp; Page Comic Shop Manager</span><span>Track every issue</span></footer>
    </main>
  </div>

  <dialog id="comicDialog" class="comic-dialog">
    <form id="comicForm" method="dialog">
      <div class="dialog-heading"><div><div class="section-kicker">COMIC CATALOG</div><h2 id="dialogTitle">Add a comic</h2></div><button class="close-button" type="button" id="closeDialog" aria-label="Close">×</button></div>
      <input type="hidden" id="comicId">
      <div class="form-row"><label>Comic title<input id="comicTitle" maxlength="180" placeholder="Batman: Year One" required></label><label>Series title<input id="seriesTitle" maxlength="140" placeholder="Batman" required></label></div>
      <div class="form-row"><label>Issue or collected range<input id="issueNumber" maxlength="30" placeholder="404–407 or Book 1" required></label><label>Store catalog code<input id="catalogCode" maxlength="30" placeholder="DC-BAT-Y1" required></label></div>
      <div class="form-row"><label>Publisher<select id="comicPublisher" required></select></label><label>Lead creator<select id="comicCreator" required></select></label></div>
      <div class="form-row"><label>Genre<select id="comicGenre" required></select></label><label>Format<select id="comicFormat" required><option>Single Issue</option><option>Trade Paperback</option><option>Graphic Novel</option><option>Hardcover</option></select></label></div>
      <div class="form-row"><label>Condition grade<select id="conditionGrade" required><option value="NM">NM · Near Mint</option><option value="VF">VF · Very Fine</option><option value="FN">FN · Fine</option><option value="VG">VG · Very Good</option><option value="G">G · Good</option></select></label><label>Store price ($)<input id="comicPrice" type="number" min="0" step="0.01" required></label></div>
      <label>Copies in stock<input id="comicStock" type="number" min="0" step="1" required></label>
      <div class="form-error" id="formError" role="alert"></div>
      <div class="dialog-actions"><button class="secondary-button" type="button" id="cancelDialog">Cancel</button><button class="primary-button" type="submit">Save comic</button></div>
    </form>
  </dialog>

  <dialog id="entityDialog" class="comic-dialog">
    <form id="entityForm" method="dialog">
      <div class="dialog-heading"><div><div class="section-kicker" id="entityTypeLabel">CATALOG SETTINGS</div><h2 id="entityDialogTitle">Add publisher</h2></div><button class="close-button" type="button" data-close-dialog="entityDialog" aria-label="Close">×</button></div>
      <input type="hidden" id="entityType"><input type="hidden" id="entityId">
      <label id="entityNameLabel">Publisher name<input id="entityName" maxlength="120" required></label>
      <div class="form-error" id="entityError" role="alert"></div>
      <div class="dialog-actions"><button class="secondary-button" type="button" data-close-dialog="entityDialog">Cancel</button><button class="primary-button" type="submit">Save</button></div>
    </form>
  </dialog>

  <dialog id="orderDialog" class="comic-dialog wide-dialog">
    <form id="orderForm" method="dialog">
      <div class="dialog-heading"><div><div class="section-kicker">SALES</div><h2>Create customer order</h2></div><button class="close-button" type="button" data-close-dialog="orderDialog" aria-label="Close">×</button></div>
      <label>Customer<select id="orderCustomer" required></select></label>
      <div class="order-items-heading"><strong>Order items</strong><button class="text-button" type="button" id="addOrderItemButton">＋ Add comic</button></div>
      <div id="orderItemInputs"></div>
      <div class="form-error" id="orderError" role="alert"></div>
      <div class="dialog-actions"><button class="secondary-button" type="button" data-close-dialog="orderDialog">Cancel</button><button class="primary-button" type="submit">Place order</button></div>
    </form>
  </dialog>
  <div class="toast" id="toast" role="status"></div>
  <script src="assets/app.js"></script>
</body>
</html>

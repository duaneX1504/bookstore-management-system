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
        <a class="nav-item active" href="#overview"><span class="nav-icon">▦</span> Overview</a>
        <a class="nav-item" href="#inventory"><span class="nav-icon">▤</span> Comic inventory <span class="nav-count" id="navComicCount">0</span></a>
        <a class="nav-item" href="#orders"><span class="nav-icon">▧</span> Orders</a>
        <a class="nav-item" href="#inventory"><span class="nav-icon">◫</span> Publishers</a>
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
        <aside class="panel orders-panel" id="orders">
          <div class="panel-heading compact"><div><div class="section-kicker">PULL DESK</div><h2>Recent orders</h2></div><a class="small-link" href="#orders">View all <span>→</span></a></div>
          <div id="orderList" class="order-list"><div class="loading-cell">Loading orders…</div></div>
          <div class="order-tip"><span>✦</span><p>New orders reserve the selected comic copies. Cancelling restores the stock.</p></div>
        </aside>
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
  <div class="toast" id="toast" role="status"></div>
  <script src="assets/app.js"></script>
</body>
</html>

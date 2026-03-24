<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$flash = consume_flash();
$categoryFilter = trim($_GET['category'] ?? '');
$maxPriceFilter = trim($_GET['max_price'] ?? '');
$lookupEmail = trim($_GET['lookup_email'] ?? '');

$params = [];
$menuSql = 'SELECT menu_item_id, name, description, price, category, availability_status FROM menu_items WHERE 1=1';

if ($categoryFilter !== '') {
  $menuSql .= ' AND category = :category';
  $params[':category'] = $categoryFilter;
}

if ($maxPriceFilter !== '' && is_numeric($maxPriceFilter)) {
  $menuSql .= ' AND price <= :max_price';
  $params[':max_price'] = (float) $maxPriceFilter;
}

$menuSql .= ' ORDER BY category, price, name';
$menuStmt = db()->prepare($menuSql);
$menuStmt->execute($params);
$menuItems = $menuStmt->fetchAll();

$categories = db()->query('SELECT DISTINCT category FROM menu_items ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);

$lookupResults = [];
if ($lookupEmail !== '' && filter_var($lookupEmail, FILTER_VALIDATE_EMAIL)) {
  $lookupStmt = db()->prepare(
    'SELECT r.reservation_id, r.reservation_date, r.reservation_time, r.guests, r.status, r.special_request,
            rt.table_name, c.name, c.email
     FROM reservations r
     JOIN customers c ON c.customer_id = r.customer_id
     LEFT JOIN restaurant_tables rt ON rt.table_id = r.table_id
     WHERE c.email = :email
     ORDER BY r.reservation_date DESC, r.reservation_time DESC'
  );
  $lookupStmt->execute([':email' => $lookupEmail]);
  $lookupResults = $lookupStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Harvest Bistro Reservation and Menu System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <header class="hero">
    <div class="container">
      <div class="hero-topbar">
        <div class="brand">
          <div class="logo" aria-hidden="true">HB</div>
          <div>
            <p class="eyebrow">Restaurant Reservation & Menu Management</p>
            <h1>Harvest Bistro Operations Portal</h1>
          </div>
        </div>
        <nav class="main-nav">
          <a href="#menu">Menu</a>
          <a href="#reservation">Reserve</a>
          <a href="#lookup">My Reservation</a>
        </nav>
      </div>

      <div class="hero-grid">
        <section class="hero-copy">
          <p class="eyebrow">Proposal-driven demo</p>
          <h2>Online reservations, role-based dashboards, and data the restaurant can actually use.</h2>
          <p class="lede">This version tracks customers, tables, reservations, menu items, staff accounts, reservation status, and admin-side dish ordering analytics in MySQL.</p>
          <div class="feature-strip">
            <span>Reduce double bookings</span>
            <span>Track peak hours</span>
            <span>Update menu quickly</span>
          </div>
        </section>

        <aside class="panel summary-panel">
          <h3>System Roles</h3>
          <ul class="plain-list">
            <li>Customers browse menus, reserve tables, and cancel bookings.</li>
            <li>Staff review reservations, confirm or cancel them, assign tables, and edit menu items.</li>
            <li>Admins review reporting metrics and manage staff accounts.</li>
          </ul>
        </aside>
      </div>
    </div>
  </header>

  <main class="container stack">
    <?php if ($flash): ?>
      <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
    <?php endif; ?>

    <section id="menu" class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Customer Menu</p>
          <h2>Browse by category or price</h2>
        </div>
      </div>

      <form method="get" class="filter-bar">
        <label>
          <span>Category</span>
          <select name="category">
            <option value="">All</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= e($category) ?>" <?= selected($category, $categoryFilter) ?>><?= e($category) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span>Max price</span>
          <input type="number" min="1" step="0.01" name="max_price" value="<?= e($maxPriceFilter) ?>" placeholder="25.00" />
        </label>
        <div class="filter-actions">
          <button type="submit">Apply Filters</button>
          <a class="ghost-button" href="index.php#menu">Reset</a>
        </div>
      </form>

      <div id="menuList" class="menu-grid">
        <?php foreach ($menuItems as $item): ?>
          <article class="menu-card" data-category="<?= e($item['category']) ?>" data-price="<?= e((string) $item['price']) ?>">
            <div class="menu-meta">
              <span class="category-chip"><?= e($item['category']) ?></span>
              <span class="status-chip <?= $item['availability_status'] === 'available' ? 'status-live' : 'status-off' ?>">
                <?= e(ucfirst($item['availability_status'])) ?>
              </span>
            </div>
            <h3><?= e($item['name']) ?></h3>
            <p><?= e($item['description']) ?></p>
            <strong>$<?= e(number_format((float) $item['price'], 2)) ?></strong>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section id="reservation" class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Reservation Form</p>
          <h2>Reserve a table online</h2>
        </div>
      </div>
      <form id="reservationForm" method="post" action="reserve.php" class="stack-form">
        <div class="grid two-up">
          <label>
            <span>Name</span>
            <input type="text" id="name" name="name" required />
          </label>
          <label>
            <span>Email</span>
            <input type="email" id="email" name="email" required />
          </label>
          <label>
            <span>Phone</span>
            <input type="text" id="phone" name="phone" required />
          </label>
          <label>
            <span>Guests</span>
            <input type="number" id="guests" name="guests" min="1" max="8" required />
          </label>
          <label>
            <span>Date</span>
            <input type="date" id="date" name="date" required />
          </label>
          <label>
            <span>Time</span>
            <input type="time" id="time" name="time" required />
          </label>
        </div>
        <label>
          <span>Special request</span>
          <input type="text" name="special_request" placeholder="Birthday, accessibility, seating preference..." />
        </label>
        <div class="filter-actions">
          <button id="reserveBtn" type="submit">Submit Reservation</button>
        </div>
        <p id="confirmation" class="confirmation" role="status" aria-live="polite"></p>
      </form>
    </section>

    <section id="lookup" class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Customer Lookup</p>
          <h2>View or cancel your reservation</h2>
        </div>
      </div>

      <form method="get" class="filter-bar">
        <label class="grow">
          <span>Email</span>
          <input type="email" name="lookup_email" value="<?= e($lookupEmail) ?>" placeholder="you@example.com" required />
        </label>
        <div class="filter-actions">
          <button type="submit">Find Reservations</button>
        </div>
      </form>

      <?php if ($lookupEmail !== '' && $lookupResults === []): ?>
        <p class="muted-copy">No reservations found for that email address.</p>
      <?php endif; ?>

      <?php if ($lookupResults !== []): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Guests</th>
                <th>Table</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lookupResults as $reservation): ?>
                <tr>
                  <td>#<?= e((string) $reservation['reservation_id']) ?></td>
                  <td><?= e($reservation['reservation_date']) ?></td>
                  <td><?= e(substr($reservation['reservation_time'], 0, 5)) ?></td>
                  <td><?= e((string) $reservation['guests']) ?></td>
                  <td><?= e($reservation['table_name'] ?? 'Pending assignment') ?></td>
                  <td><span class="badge <?= reservation_status_badge($reservation['status']) ?>"><?= e(ucfirst($reservation['status'])) ?></span></td>
                  <td>
                    <?php if ($reservation['status'] !== 'cancelled'): ?>
                      <form method="post" action="cancel_reservation.php">
                        <input type="hidden" name="reservation_id" value="<?= e((string) $reservation['reservation_id']) ?>" />
                        <input type="hidden" name="email" value="<?= e($lookupEmail) ?>" />
                        <button type="submit" class="danger-button">Cancel</button>
                      </form>
                    <?php else: ?>
                      <span class="muted-copy">Already cancelled</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-inner">
      <span class="muted-copy">Harvest Bistro demo system</span>
      <a class="footer-link" href="login.php">Admin login</a>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>

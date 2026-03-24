<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$user = require_role(['admin']);
$flash = consume_flash();

$totals = db()->query(
  'SELECT
      COUNT(*) AS total_reservations,
      SUM(status = "confirmed") AS confirmed_count,
      SUM(status = "pending") AS pending_count,
      SUM(status = "cancelled") AS cancelled_count
   FROM reservations'
)->fetch();

$peakHours = db()->query(
  'SELECT DATE_FORMAT(reservation_time, "%H:00") AS reservation_hour, COUNT(*) AS total
   FROM reservations
   GROUP BY reservation_hour
   ORDER BY total DESC, reservation_hour ASC
   LIMIT 5'
)->fetchAll();

$peakDays = db()->query(
  'SELECT DAYNAME(reservation_date) AS reservation_day, COUNT(*) AS total
   FROM reservations
   GROUP BY reservation_day
   ORDER BY total DESC'
)->fetchAll();

$popularMenuItems = db()->query(
  'SELECT mi.name, mi.category, SUM(mio.quantity) AS total_quantity
   FROM menu_item_orders mio
   JOIN menu_items mi ON mi.menu_item_id = mio.menu_item_id
   GROUP BY mi.menu_item_id, mi.name, mi.category
   ORDER BY total_quantity DESC, mi.name ASC
   LIMIT 5'
)->fetchAll();

$hourlyOrders = db()->query(
  'SELECT DATE_FORMAT(ordered_at, "%H:00") AS order_hour, SUM(quantity) AS total_quantity
   FROM menu_item_orders
   GROUP BY order_hour
   ORDER BY order_hour ASC'
)->fetchAll();

$staffAccounts = db()->query(
  'SELECT staff_id, name, email, role, created_at
   FROM staff_accounts
   ORDER BY role DESC, name ASC'
)->fetchAll();

$menuItems = db()->query(
  'SELECT menu_item_id, name, category
   FROM menu_items
   ORDER BY category, name'
)->fetchAll();

$reservations = db()->query(
  'SELECT reservation_id
   FROM reservations
   ORDER BY reservation_date DESC, reservation_time DESC'
)->fetchAll(PDO::FETCH_COLUMN);

$recentOrders = db()->query(
  'SELECT mio.order_id, mi.name, mio.quantity, mio.ordered_at, r.reservation_id, sa.name AS recorded_by
   FROM menu_item_orders mio
   JOIN menu_items mi ON mi.menu_item_id = mio.menu_item_id
   LEFT JOIN reservations r ON r.reservation_id = mio.reservation_id
   LEFT JOIN staff_accounts sa ON sa.staff_id = mio.recorded_by_staff_id
   ORDER BY mio.ordered_at DESC
   LIMIT 10'
)->fetchAll();

$maxDishCount = 1;
foreach ($popularMenuItems as $item) {
  $maxDishCount = max($maxDishCount, (int) $item['total_quantity']);
}

$maxHourlyCount = 1;
foreach ($hourlyOrders as $hour) {
  $maxHourlyCount = max($maxHourlyCount, (int) $hour['total_quantity']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container stack">
    <section class="dashboard-header">
      <div>
        <p class="eyebrow">Admin Dashboard</p>
        <h1>Reporting and staff management</h1>
      </div>
      <div class="dashboard-links">
        <a class="ghost-button" href="staff.php">Staff Dashboard</a>
        <a class="ghost-button" href="index.php">Customer Page</a>
        <a class="ghost-button" href="logout.php">Log Out</a>
      </div>
    </section>

    <?php if ($flash): ?>
      <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
    <?php endif; ?>

    <section class="grid metrics-grid">
      <article class="metric-card">
        <span>Total Reservations</span>
        <strong><?= e((string) ($totals['total_reservations'] ?? 0)) ?></strong>
      </article>
      <article class="metric-card">
        <span>Confirmed</span>
        <strong><?= e((string) ($totals['confirmed_count'] ?? 0)) ?></strong>
      </article>
      <article class="metric-card">
        <span>Pending</span>
        <strong><?= e((string) ($totals['pending_count'] ?? 0)) ?></strong>
      </article>
      <article class="metric-card">
        <span>Cancelled</span>
        <strong><?= e((string) ($totals['cancelled_count'] ?? 0)) ?></strong>
      </article>
    </section>

    <section class="grid dashboard-grid">
      <article class="panel">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Busy Hours</p>
            <h2>Peak reservation times</h2>
          </div>
        </div>
        <ul class="plain-list">
          <?php foreach ($peakHours as $hour): ?>
            <li><?= e($hour['reservation_hour']) ?> - <?= e((string) $hour['total']) ?> reservations</li>
          <?php endforeach; ?>
        </ul>
      </article>

      <article class="panel">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Peak Days</p>
            <h2>Busiest reservation days</h2>
          </div>
        </div>
        <ul class="plain-list">
          <?php foreach ($peakDays as $day): ?>
            <li><?= e($day['reservation_day']) ?> - <?= e((string) $day['total']) ?> reservations</li>
          <?php endforeach; ?>
        </ul>
      </article>

      <article class="panel">
        <div class="section-heading">
          <div>
            <p class="eyebrow">Popular Menu Items</p>
            <h2>Actual dish orders logged by staff</h2>
          </div>
        </div>
        <div class="chart-list">
          <?php foreach ($popularMenuItems as $item): ?>
            <div class="chart-row">
              <div class="chart-label"><?= e($item['name']) ?> <span class="muted-copy">(<?= e($item['category']) ?>)</span></div>
              <div class="chart-bar-shell">
                <div class="chart-bar" style="width: <?= e((string) max(12, (int) round(((int) $item['total_quantity'] / $maxDishCount) * 100))) ?>%"></div>
              </div>
              <strong><?= e((string) $item['total_quantity']) ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
      </article>
    </section>

    <section id="order-analytics" class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Order Analytics</p>
          <h2>Record dish orders and view service trends</h2>
        </div>
      </div>
      <div class="grid dashboard-grid">
        <form method="post" action="staff_actions.php" class="stack-form">
          <input type="hidden" name="action" value="record_order" />
          <label>
            <span>Dish</span>
            <select name="menu_item_id" required>
              <option value="">Select a menu item</option>
              <?php foreach ($menuItems as $item): ?>
                <option value="<?= e((string) $item['menu_item_id']) ?>"><?= e($item['name']) ?> (<?= e($item['category']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            <span>Reservation ID (optional)</span>
            <select name="reservation_id">
              <option value="0">Walk-in / no reservation</option>
              <?php foreach ($reservations as $reservationId): ?>
                <option value="<?= e((string) $reservationId) ?>">#<?= e((string) $reservationId) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            <span>Quantity</span>
            <input type="number" name="quantity" min="1" value="1" required />
          </label>
          <label>
            <span>Ordered at</span>
            <input type="datetime-local" name="ordered_at" required />
          </label>
          <button type="submit">Record Order</button>
        </form>

        <div class="panel inset-panel">
          <p class="eyebrow">Orders By Hour</p>
          <h3>Service timeline</h3>
          <div class="chart-list">
            <?php foreach ($hourlyOrders as $hour): ?>
              <div class="chart-row">
                <div class="chart-label"><?= e($hour['order_hour']) ?></div>
                <div class="chart-bar-shell">
                  <div class="chart-bar alt-bar" style="width: <?= e((string) max(10, (int) round(((int) $hour['total_quantity'] / $maxHourlyCount) * 100))) ?>%"></div>
                </div>
                <strong><?= e((string) $hour['total_quantity']) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="table-wrap analytics-table">
        <table>
          <thead>
            <tr>
              <th>Dish</th>
              <th>Qty</th>
              <th>Ordered At</th>
              <th>Reservation</th>
              <th>Recorded By</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $order): ?>
              <tr>
                <td><?= e($order['name']) ?></td>
                <td><?= e((string) $order['quantity']) ?></td>
                <td><?= e($order['ordered_at']) ?></td>
                <td><?= $order['reservation_id'] ? '#' . e((string) $order['reservation_id']) : 'Walk-in' ?></td>
                <td><?= e($order['recorded_by'] ?? 'Unknown') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section id="staff-accounts" class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Staff Accounts</p>
          <h2>Manage who can log in</h2>
        </div>
      </div>
      <div class="grid dashboard-grid">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($staffAccounts as $account): ?>
                <tr>
                  <td><?= e($account['name']) ?></td>
                  <td><?= e($account['email']) ?></td>
                  <td><?= e(ucfirst($account['role'])) ?></td>
                  <td><?= e($account['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <form method="post" action="staff_actions.php" class="stack-form">
          <input type="hidden" name="action" value="create_staff" />
          <label>
            <span>Name</span>
            <input type="text" name="name" required />
          </label>
          <label>
            <span>Email</span>
            <input type="email" name="email" required />
          </label>
          <label>
            <span>Role</span>
            <select name="role">
              <option value="staff">Staff</option>
              <option value="admin">Admin</option>
            </select>
          </label>
          <label>
            <span>Password</span>
            <input type="password" name="password" minlength="8" required />
          </label>
          <button type="submit">Create Account</button>
        </form>
      </div>
    </section>
  </main>
</body>
</html>

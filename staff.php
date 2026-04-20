<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$user = require_role(['staff', 'admin']);
$flash = consume_flash();

$reservations = db()->query(
  'SELECT r.reservation_id, r.reservation_date, r.reservation_time, r.guests, r.status, r.special_request,
          c.name AS customer_name, c.email, rt.table_name, rt.table_id
   FROM reservations r
   JOIN customers c ON c.customer_id = r.customer_id
   LEFT JOIN restaurant_tables rt ON rt.table_id = r.table_id
   ORDER BY r.reservation_date ASC, r.reservation_time ASC'
)->fetchAll();

$tables = db()->query(
  'SELECT table_id, table_name, seats, location, availability_status
   FROM restaurant_tables
   ORDER BY seats, table_name'
)->fetchAll();

$menuItems = db()->query(
  'SELECT menu_item_id, name, description, price, category, availability_status
   FROM menu_items
   ORDER BY category, name'
)->fetchAll();

$menuCategories = [
  'Appetizer',
  'Main',
  'Entree',
  'Dessert',
  'Drinks',
];

function format_staff_time(string $timeValue): string {
  return date('g:i A', strtotime($timeValue));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staff Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container stack">
    <section class="dashboard-header">
      <div>
        <p class="eyebrow">Staff Dashboard</p>
        <h1>Operations for <?= e($user['name']) ?></h1>
      </div>
      <div class="dashboard-links">
        <?php if ($user['role'] === 'admin'): ?>
          <a class="ghost-button" href="admin.php">Admin Dashboard</a>
        <?php endif; ?>
        <a class="ghost-button" href="index.php">Customer Page</a>
        <a class="ghost-button" href="logout.php">Log Out</a>
      </div>
    </section>

    <?php if ($flash): ?>
      <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
    <?php endif; ?>

    <section class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Reservations</p>
          <h2>Confirm, cancel, or assign tables</h2>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Reservation</th>
              <th>Guest</th>
              <th>Contact</th>
              <th>Guests</th>
              <th>Status</th>
              <th>Assign Table</th>
              <th>Save</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $reservation): ?>
              <tr>
                <td>#<?= e((string) $reservation['reservation_id']) ?><br><?= e($reservation['reservation_date']) ?> <?= e(format_staff_time($reservation['reservation_time'])) ?></td>
                <td><?= e($reservation['customer_name']) ?><br><span class="muted-copy"><?= e($reservation['special_request']) ?></span></td>
                <td><?= e($reservation['email']) ?></td>
                <td><?= e((string) $reservation['guests']) ?></td>
                <td>
                  <form method="post" action="staff_actions.php" class="inline-form">
                    <input type="hidden" name="action" value="update_reservation" />
                    <input type="hidden" name="reservation_id" value="<?= e((string) $reservation['reservation_id']) ?>" />
                    <select name="status">
                      <option value="pending" <?= selected('pending', $reservation['status']) ?>>Pending</option>
                      <option value="confirmed" <?= selected('confirmed', $reservation['status']) ?>>Confirmed</option>
                      <option value="cancelled" <?= selected('cancelled', $reservation['status']) ?>>Cancelled</option>
                    </select>
                </td>
                <td>
                    <select name="table_id">
                      <option value="0">Unassigned</option>
                      <?php foreach ($tables as $table): ?>
                        <option value="<?= e((string) $table['table_id']) ?>" <?= (string) $table['table_id'] === (string) ($reservation['table_id'] ?? '') ? 'selected' : '' ?>>
                          <?= e($table['table_name']) ?> (<?= e((string) $table['seats']) ?> seats)
                        </option>
                      <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <button type="submit">Update</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section id="menu-editor" class="panel">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Menu Editor</p>
          <h2>Update or add menu items</h2>
        </div>
      </div>
      <div class="grid dashboard-grid">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Item</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Remove</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($menuItems as $item): ?>
                <tr>
                  <td><?= e($item['name']) ?></td>
                  <td><?= e($item['category']) ?></td>
                  <td>$<?= e(number_format((float) $item['price'], 2)) ?></td>
                  <td><?= e(ucfirst($item['availability_status'])) ?></td>
                  <td>
                    <form method="post" action="staff_actions.php" onsubmit="return confirm('Remove this menu item?');">
                      <input type="hidden" name="action" value="delete_menu" />
                      <input type="hidden" name="menu_item_id" value="<?= e((string) $item['menu_item_id']) ?>" />
                      <button type="submit" class="danger-button">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <form method="post" action="staff_actions.php" class="stack-form">
          <input type="hidden" name="action" value="update_menu" />
          <label>
            <span>Existing menu item (optional)</span>
            <select name="menu_item_id">
              <option value="0">Create a new menu item</option>
              <?php foreach ($menuItems as $item): ?>
                <option value="<?= e((string) $item['menu_item_id']) ?>">
                  <?= e($item['name']) ?> (<?= e($item['category']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            <span>Name</span>
            <input type="text" name="name" required />
          </label>
          <label>
            <span>Description</span>
            <textarea name="description" rows="4" required></textarea>
          </label>
          <label>
            <span>Price</span>
            <input type="number" name="price" min="0.01" step="0.01" required />
          </label>
          <label>
            <span>Category</span>
            <select name="category" required>
              <option value="">Select a category</option>
              <?php foreach ($menuCategories as $category): ?>
                <option value="<?= e($category) ?>"><?= e($category) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label>
            <span>Status</span>
            <select name="availability_status">
              <option value="available">Available</option>
              <option value="unavailable">Unavailable</option>
            </select>
          </label>
          <button type="submit">Save Menu Item</button>
        </form>
      </div>
    </section>
  </main>
</body>
</html>

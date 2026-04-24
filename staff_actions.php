<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$user = require_role(['staff', 'admin']);
$action = $_POST['action'] ?? '';

if ($action === 'update_reservation') {
  $reservationId = (int) ($_POST['reservation_id'] ?? 0);
  $status = $_POST['status'] ?? 'pending';
  $tableId = (int) ($_POST['table_id'] ?? 0);

  $allowedStatuses = ['pending', 'confirmed', 'cancelled'];
  if ($reservationId < 1 || !in_array($status, $allowedStatuses, true)) {
    flash('error', 'Invalid reservation update.');
    redirect('staff.php');
  }

  $stmt = db()->prepare(
    'UPDATE reservations
     SET status = :status, table_id = :table_id
     WHERE reservation_id = :reservation_id'
  );
  $stmt->execute([
    ':status' => $status,
    ':table_id' => $tableId > 0 ? $tableId : null,
    ':reservation_id' => $reservationId,
  ]);

  flash('success', 'Reservation updated.');
  redirect($user['role'] === 'admin' ? 'admin.php' : 'staff.php');
}

if ($action === 'update_menu') {
  $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = (float) ($_POST['price'] ?? 0);
  $category = trim($_POST['category'] ?? '');
  $availabilityStatus = $_POST['availability_status'] ?? 'available';

  if ($name === '' || $description === '' || $price <= 0 || $category === '') {
    flash('error', 'Menu items need a name, description, price, and category.');
    redirect('admin.php#menu-editor');
  }

  if ($menuItemId > 0) {
    $stmt = db()->prepare(
      'UPDATE menu_items
       SET name = :name, description = :description, price = :price, category = :category,
           availability_status = :availability_status
       WHERE menu_item_id = :menu_item_id'
    );
    $stmt->execute([
      ':name' => $name,
      ':description' => $description,
      ':price' => $price,
      ':category' => $category,
      ':availability_status' => $availabilityStatus,
      ':menu_item_id' => $menuItemId,
    ]);
    flash('success', 'Menu item updated.');
  } else {
    $stmt = db()->prepare(
      'INSERT INTO menu_items (name, description, price, category, availability_status)
       VALUES (:name, :description, :price, :category, :availability_status)'
    );
    $stmt->execute([
      ':name' => $name,
      ':description' => $description,
      ':price' => $price,
      ':category' => $category,
      ':availability_status' => $availabilityStatus,
    ]);
    flash('success', 'Menu item added.');
  }

  redirect('admin.php#menu-editor');
}

if ($action === 'delete_menu') {
  $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);

  if ($menuItemId < 1) {
    flash('error', 'Invalid menu item.');
    redirect('admin.php#menu-editor');
  }

  $stmt = db()->prepare('DELETE FROM menu_items WHERE menu_item_id = :menu_item_id');
  $stmt->execute([
    ':menu_item_id' => $menuItemId,
  ]);

  flash('success', 'Menu item removed.');
  redirect('admin.php#menu-editor');
}

if ($action === 'create_staff') {
  require_role(['admin']);

  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $role = $_POST['role'] ?? 'staff';
  $password = $_POST['password'] ?? '';

  if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, ['staff', 'admin'], true) || strlen($password) < 8) {
    flash('error', 'Staff accounts need a valid name, email, role, and password of at least 8 characters.');
    redirect('admin.php#staff-accounts');
  }

  $stmt = db()->prepare(
    'INSERT INTO staff_accounts (name, email, role, password_hash)
     VALUES (:name, :email, :role, :password_hash)'
  );
  $stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':role' => $role,
    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
  ]);

  flash('success', 'Staff account created.');
  redirect('admin.php#staff-accounts');
}

if ($action === 'record_order') {
  require_role(['staff', 'admin']);

  $menuItemId = (int) ($_POST['menu_item_id'] ?? 0);
  $reservationId = (int) ($_POST['reservation_id'] ?? 0);
  $quantity = (int) ($_POST['quantity'] ?? 0);
  $orderedTime = trim($_POST['ordered_time'] ?? '');
  $allowedOrderTimes = [];
  for ($hour = 11; $hour <= 21; $hour++) {
    $allowedOrderTimes[] = sprintf('%02d:00', $hour);
  }

  if ($menuItemId < 1 || $quantity < 1 || !in_array($orderedTime, $allowedOrderTimes, true)) {
    flash('error', 'Order logs need a menu item, quantity, and an hourly time between 11:00 AM and 9:00 PM.');
    redirect('staff.php#record-orders');
  }

  $orderedAt = date('Y-m-d') . ' ' . $orderedTime . ':00';

  $stmt = db()->prepare(
    'INSERT INTO menu_item_orders (menu_item_id, reservation_id, quantity, ordered_at, recorded_by_staff_id)
     VALUES (:menu_item_id, :reservation_id, :quantity, :ordered_at, :recorded_by_staff_id)'
  );
  $stmt->execute([
    ':menu_item_id' => $menuItemId,
    ':reservation_id' => $reservationId > 0 ? $reservationId : null,
    ':quantity' => $quantity,
    ':ordered_at' => $orderedAt,
    ':recorded_by_staff_id' => $user['staff_id'],
  ]);

  flash('success', 'Order record saved for analytics.');
  redirect('staff.php#record-orders');
}

flash('error', 'Unsupported action.');
redirect('staff.php');

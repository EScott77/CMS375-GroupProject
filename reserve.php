<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function fail_response(string $message, int $status = 400): void {
  http_response_code($status);

  $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => $message]);
    exit;
  }

  flash('error', $message);
  redirect('index.php#reservation');
}

function respond_success(string $message, array $payload = []): void {
  $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

  if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'message' => $message, 'data' => $payload]);
    exit;
  }

  flash('success', $message);
  redirect('index.php#reservation');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$guests = (int) ($_POST['guests'] ?? 0);
$specialRequest = trim($_POST['special_request'] ?? '');

if (
  $name === '' ||
  !filter_var($email, FILTER_VALIDATE_EMAIL) ||
  $phone === '' ||
  $date === '' ||
  $time === '' ||
  $guests < 1
) {
  fail_response('Please complete all reservation fields with valid values.');
}

try {
  $pdo = db();
  $pdo->beginTransaction();

  $customerStmt = $pdo->prepare('SELECT customer_id FROM customers WHERE email = :email');
  $customerStmt->execute([':email' => $email]);
  $customerId = $customerStmt->fetchColumn();

  if ($customerId) {
    $updateCustomer = $pdo->prepare(
      'UPDATE customers SET name = :name, phone = :phone WHERE customer_id = :customer_id'
    );
    $updateCustomer->execute([
      ':name' => $name,
      ':phone' => $phone,
      ':customer_id' => $customerId,
    ]);
  } else {
    $insertCustomer = $pdo->prepare(
      'INSERT INTO customers (name, email, phone) VALUES (:name, :email, :phone)'
    );
    $insertCustomer->execute([
      ':name' => $name,
      ':email' => $email,
      ':phone' => $phone,
    ]);
    $customerId = (int) $pdo->lastInsertId();
  }

  $tableStmt = $pdo->prepare(
    'SELECT rt.table_id
     FROM restaurant_tables rt
     WHERE rt.seats >= :guests
       AND rt.availability_status = "available"
       AND rt.table_id NOT IN (
         SELECT r.table_id
         FROM reservations r
         WHERE r.reservation_date = :reservation_date
           AND r.reservation_time = :reservation_time
           AND r.status IN ("pending", "confirmed")
           AND r.table_id IS NOT NULL
       )
     ORDER BY rt.seats ASC
     LIMIT 1'
  );
  $tableStmt->execute([
    ':guests' => $guests,
    ':reservation_date' => $date,
    ':reservation_time' => $time,
  ]);
  $tableId = $tableStmt->fetchColumn();

  $reservationStmt = $pdo->prepare(
    'INSERT INTO reservations (customer_id, table_id, reservation_date, reservation_time, guests, status, special_request)
     VALUES (:customer_id, :table_id, :reservation_date, :reservation_time, :guests, :status, :special_request)'
  );
  $reservationStmt->execute([
    ':customer_id' => $customerId,
    ':table_id' => $tableId ?: null,
    ':reservation_date' => $date,
    ':reservation_time' => $time,
    ':guests' => $guests,
    ':status' => $tableId ? 'confirmed' : 'pending',
    ':special_request' => $specialRequest,
  ]);

  $reservationId = (int) $pdo->lastInsertId();

  $pdo->commit();

  $statusMessage = $tableId
    ? 'Reservation confirmed and table assigned.'
    : 'Reservation received and marked pending until staff assigns a table.';

  respond_success($statusMessage, ['reservation_id' => $reservationId]);
} catch (Throwable $e) {
  if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
    $pdo->rollBack();
  }

  fail_response('Unable to save reservation right now.', 500);
}

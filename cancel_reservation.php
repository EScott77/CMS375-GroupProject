<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$reservationId = (int) ($_POST['reservation_id'] ?? 0);
$email = trim($_POST['email'] ?? '');

if ($reservationId < 1 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  flash('error', 'A valid reservation and email are required to cancel.');
  redirect('index.php#lookup');
}

$stmt = db()->prepare(
  'UPDATE reservations r
   JOIN customers c ON c.customer_id = r.customer_id
   SET r.status = "cancelled"
   WHERE r.reservation_id = :reservation_id
     AND c.email = :email'
);

$stmt->execute([
  ':reservation_id' => $reservationId,
  ':email' => $email,
]);

if ($stmt->rowCount() === 0) {
  flash('error', 'Reservation not found for that email address.');
  redirect('index.php#lookup');
}

flash('success', 'Reservation cancelled.');
redirect('index.php#lookup');

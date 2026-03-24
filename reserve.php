<?php
header('Content-Type: application/json; charset=utf-8');

function bad($msg, $status = 400) {
  http_response_code($status);
  echo json_encode(['ok' => false, 'error' => $msg]);
  exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$guests = (int) ($_POST['guests'] ?? 0);

if (
  $name === '' ||
  $email === '' ||
  $date === '' ||
  $time === '' ||
  $guests < 1 ||
  !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
  bad('Please complete all fields with valid values.');
}

$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = 'restaurant_db';
$dbUser = 'root';
$dbPass = '';

try {
  $pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName),
    $dbUser,
    $dbPass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  $stmt = $pdo->prepare(
    'INSERT INTO reservations (name, email, reservation_date, reservation_time, guests)
     VALUES (:name, :email, :reservation_date, :reservation_time, :guests)'
  );

  $stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':reservation_date' => $date,
    ':reservation_time' => $time,
    ':guests' => $guests,
  ]);
} catch (PDOException $e) {
  bad('Database error. Check your MySQL settings in reserve.php.', 500);
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
  echo json_encode([
    'ok' => true,
    'reservation' => [
      'name' => $name,
      'email' => $email,
      'date' => $date,
      'time' => $time,
      'guests' => $guests,
    ],
  ]);
  exit;
}

header('Location: ./index.php?success=1&name=' . urlencode($name));
exit;

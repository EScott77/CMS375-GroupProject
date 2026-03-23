<?php
<?php
// Basic server-side reservation endpoint.
// Stores reservations in data/reservations.json (create data/ first).

header('Content-Type: application/json; charset=utf-8');

function bad($msg, $status = 400){
  http_response_code($status);
  echo json_encode(['ok'=>false,'error'=>$msg]);
  exit;
}

$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? '');
$email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '');
$date = trim($_POST['date'] ?? '');
$time = trim($_POST['time'] ?? '');
$guests = intval($_POST['guests'] ?? 0);

if (!$name || !$email || !$date || !$time || $guests < 1) {
  bad('Please complete all fields with valid values.');
}

$reservation = [
  'name'=>$name,
  'email'=>$email,
  'date'=>$date,
  'time'=>$time,
  'guests'=>$guests,
  'createdAt'=>date(DATE_ATOM)
];

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
  mkdir($dataDir, 0755, true);
}
$file = $dataDir . '/reservations.json';
$all = [];
if (file_exists($file)) {
  $content = file_get_contents($file);
  $all = json_decode($content, true) ?: [];
}
$all[] = $reservation;
// write atomically with lock
$temp = $file . '.tmp';
if (file_put_contents($temp, json_encode($all, JSON_PRETTY_PRINT)) === false) {
  bad('Unable to save reservation.', 500);
}
rename($temp, $file);

// If request was AJAX, return JSON. Otherwise redirect back with query params.
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($isAjax) {
  echo json_encode(['ok'=>true,'reservation'=>$reservation]);
  exit;
} else {
  // simple redirect back to index with small flash (avoid exposing email)
  header('Location: ./index.php?success=1&name=' . urlencode($name));
  exit;
}
?>
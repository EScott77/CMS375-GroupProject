<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($requestMethod === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = db()->prepare('SELECT staff_id, name, email, role, password_hash FROM staff_accounts WHERE email = :email');
  $stmt->execute([':email' => $email]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = [
      'staff_id' => $user['staff_id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'role' => $user['role'],
    ];

    if ($user['role'] === 'admin') {
      redirect('admin.php');
    }

    redirect('staff.php');
  }

  flash('error', 'Invalid email or password.');
  redirect('login.php');
}

$flash = consume_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staff Login</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <main class="container auth-shell">
    <section class="panel auth-panel">
      <p class="eyebrow">Harvest Bistro</p>
      <h1>Staff and Admin Login</h1>
      <p class="muted-copy">Demo credentials from the seed data: `staff@harvestbistro.test` or `admin@harvestbistro.test`, password `password123`.</p>
      <?php if ($flash): ?>
        <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
      <?php endif; ?>
      <form method="post" class="stack-form">
        <label>
          <span>Email</span>
          <input type="email" name="email" required />
        </label>
        <label>
          <span>Password</span>
          <input type="password" name="password" required />
        </label>
        <button type="submit">Sign In</button>
      </form>
      <p><a href="index.php">Back to customer page</a></p>
    </section>
  </main>
</body>
</html>

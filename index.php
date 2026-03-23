<?php
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Harvest Bistro — Reservations & Menu</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <meta name="theme-color" content="#2b2b2b">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<?php
// server-side menu: keep in sync with client if needed
$menuItems = [
  ['name'=>'Burger','price'=>10,'category'=>'Main'],
  ['name'=>'Salad','price'=>8,'category'=>'Appetizer'],
  ['name'=>'Pasta','price'=>11,'category'=>'Main'],
];

function formatPrice($amount){
  return '$' . number_format((float)$amount, 2);
}
?>
<header class="site-header">
  <div class="header-inner">
    <div class="brand">
      <div class="logo" aria-hidden="true">HB</div>
      <div>
        <h1>Restaurant Name</h1>
        <p class="tagline">Sample Tagline</p>
      </div>
    </div>

    <div class="contact">
      <div class="phone">Call to book: <a href="tel:+1234567890">+1 (234) 567-890</a></div>
      <nav class="main-nav" aria-label="Primary">
        <a href="#menu">Menu</a>
        <a href="#reservation">Reservations</a>
      </nav>
    </div>
  </div>
</header>

<main class="container">

  <section id="menu" class="menu">
    <h2>Menu</h2>
    <div id="menuList" class="menu-grid" role="list">
      <?php foreach($menuItems as $item): ?>
        <div class="menu-item" role="listitem">
          <h3><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?> <span class="price"><?= htmlspecialchars(formatPrice($item['price']), ENT_QUOTES, 'UTF-8') ?></span></h3>
          <p>Delicious <?= htmlspecialchars(strtolower($item['name']), ENT_QUOTES, 'UTF-8') ?> prepared fresh to order.</p>
          <span class="category"><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="reservation" class="reservation">
    <h2>Make a Reservation</h2>
    <form id="reservationForm" aria-labelledby="reservation" method="post" action="reserve.php">
      <div class="form-grid">
        <input type="text" id="name" name="name" placeholder="Your Name" required />
        <input type="email" id="email" name="email" placeholder="Email" required />
        <input type="date" id="date" name="date" required />
        <input type="time" id="time" name="time" required />
        <input type="number" id="guests" name="guests" placeholder="Number of Guests" min="1" required />
      </div>
      <div class="form-actions">
        <button id="reserveBtn" type="submit">Reserve a Table</button>
      </div>
    </form>
    <p id="confirmation" class="confirmation" role="status" aria-live="polite">
      <?php
      if (!empty($_GET['success']) && !empty($_GET['name'])) {
        echo 'Reservation confirmed for ' . htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8') . '.';
      }
      ?>
    </p>
  </section>

</main>

<footer class="site-footer">
  <div class="container">
    <small>&copy; <span id="year"><?php echo date('Y'); ?></span> Harvest Bistro — 123 Main St • Open daily 11am–10pm</small>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>
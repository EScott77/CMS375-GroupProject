<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$menuItems = db()->query(
  'SELECT menu_item_id, name, description, price, category, availability_status
   FROM menu_items
   ORDER BY category, price, name'
)->fetchAll();

$menuImages = [
  'harvest burger' => 'images/burger.jpg',
  'wood-fired pizza' => 'images/pizza.jpg',
  'spring salad' => 'images/springSalad.jpg',
  'braised pasta' => 'images/pasta.jpg',
  'roasted salmon' => 'images/braisedSalmon.jpg',
  'chocolate torte' => 'images/darkChoc.jpg',
];

function menu_category_label_page(string $category): string {
  $normalized = strtolower(trim($category));

  return match ($normalized) {
    'main' => 'Main Courses',
    'appetizer' => 'Appetizers',
    'dessert' => 'Desserts',
    'drink', 'drinks', 'beverage' => 'Drinks',
    default => ucwords($category),
  };
}

function menu_filter_slug_page(string $category): string {
  $normalized = strtolower(trim($category));

  return match ($normalized) {
    'main' => 'main',
    'appetizer' => 'appetizer',
    'dessert' => 'dessert',
    'drink', 'drinks', 'beverage' => 'drinks',
    default => preg_replace('/[^a-z0-9]+/', '-', $normalized) ?: 'other',
  };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Harvest Bistro — Full Menu</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="top-bar">
    <div class="container top-bar-inner">
      <p>Open Daily: 11:00 AM - 10:00 PM</p>
      <p>123 Main St, Orlando, FL</p>
      <p><a href="tel:+1234567890">+1 (234) 567-890</a></p>
    </div>
  </div>

  <header class="site-header">
    <div class="container header-inner">
      <a href="index.php" class="brand">
        <div class="logo">HB</div>
        <div class="brand-copy">
          <h1>Harvest Bistro</h1>
          <p class="tagline">Seasonal dining, seamless reservations, smarter restaurant management</p>
        </div>
      </a>

      <nav class="main-nav">
        <a href="index.php#featured">Featured</a>
        <a href="index.php#reservation">Reservations</a>
        <a href="index.php#lookup">My Reservation</a>
        <a href="index.php#about">About</a>
        <a href="index.php#contact">Contact</a>
      </nav>

      <a href="index.php#reservation" class="nav-cta">Book a Table</a>
    </div>
  </header>

  <main>
    <section class="menu-page-hero">
      <div class="container">
        <div class="section-heading">
          <span class="section-label">Full Menu</span>
          <h2>Explore everything currently available</h2>
          <p>These items are loaded from the restaurant database and reflect current menu availability.</p>
        </div>

        <div class="menu-toolbar" aria-label="Menu filters">
          <button type="button" class="filter-pill active" data-filter="all">All</button>
          <button type="button" class="filter-pill" data-filter="appetizer">Appetizers</button>
          <button type="button" class="filter-pill" data-filter="main">Main Courses</button>
          <button type="button" class="filter-pill" data-filter="dessert">Desserts</button>
          <button type="button" class="filter-pill" data-filter="drinks">Drinks</button>
        </div>
      </div>
    </section>

    <section class="menu-section menu-page-section">
      <div class="container">
        <div id="menuList" class="menu-grid" role="list" aria-label="Full menu items">
          <?php foreach ($menuItems as $item): ?>
            <?php $filterSlug = menu_filter_slug_page($item['category']); ?>
            <?php $menuImage = $menuImages[strtolower($item['name'])] ?? 'images/springSalad.jpg'; ?>
            <article class="menu-card" role="listitem" data-category="<?= e($filterSlug) ?>">
              <img class="menu-card-image" src="<?= e($menuImage) ?>" alt="<?= e($item['name']) ?>" />
              <div class="menu-card-content">
                <div class="menu-meta">
                  <span class="category-chip"><?= e(menu_category_label_page($item['category'])) ?></span>
                  <span class="badge <?= $item['availability_status'] === 'available' ? 'badge-confirmed' : 'badge-cancelled' ?>">
                    <?= e(ucfirst($item['availability_status'])) ?>
                  </span>
                </div>
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['description']) ?></p>
                <strong>$<?= e(number_format((float) $item['price'], 2)) ?></strong>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="section-cta">
          <a href="index.php#reservation" class="btn btn-primary">Reserve a Table</a>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <div class="logo" aria-hidden="true">HB</div>
        <div>
          <h3>Harvest Bistro</h3>
          <p>Seasonal dining with smart reservation and menu management.</p>
        </div>
      </div>

      <div class="footer-links">
        <h4>Quick Links</h4>
        <a href="index.php#featured">Featured</a>
        <a href="index.php#reservation">Reservations</a>
        <a href="index.php#lookup">My Reservation</a>
        <a href="index.php#about">About</a>
      </div>

      <div class="footer-links">
        <h4>Contact</h4>
        <a href="tel:+1234567890">+1 (234) 567-890</a>
        <a href="mailto:hello@harvestbistro.com">hello@harvestbistro.com</a>
        <span>123 Main St, Orlando, FL</span>
      </div>

      <div class="footer-links">
        <h4>Admin</h4>
        <a href="login.php">Admin Login</a>
        <span class="footer-note">Staff and admin dashboards are available after login.</span>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <small>&copy; <span id="year"></span> Harvest Bistro — Restaurant Reservation & Menu Management System</small>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>

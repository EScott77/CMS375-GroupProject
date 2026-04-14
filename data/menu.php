<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';

$menuStmt = db()->query(
  'SELECT menu_item_id, name, description, price, category, availability_status
   FROM menu_items
   ORDER BY category, price, name'
);
$menuItems = $menuStmt->fetchAll();

$menuImages = [
  'harvest burger' => 'images/burger.jpg',
  'wood-fired pizza' => 'images/pizza.jpg',
  'spring salad' => 'images/springSalad.jpg',
  'braised pasta' => 'images/pasta.jpg',
  'roasted salmon' => 'images/braisedSalmon.jpg',
  'chocolate torte' => 'images/darkChoc.jpg',
];

function menu_category_label(string $category): string {
  $normalized = strtolower(trim($category));
  return match ($normalized) {
    'main' => 'Main Courses',
    'appetizer' => 'Appetizers',
    'dessert' => 'Desserts',
    'drink', 'drinks', 'beverage' => 'Drinks',
    default => ucwords($category),
  };
}

function menu_filter_slug(string $category): string {
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
  <meta charset="UTF-8">
  <title>Harvest Bistro — Menu</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
  <div class="container header-inner">
    <a href="index.php" class="brand">
      <div class="logo">HB</div>
      <div class="brand-copy">
        <h1>Harvest Bistro</h1>
      </div>
    </a>

    <nav class="main-nav">
      <a href="index.php#featured">Featured</a>
      <a href="menu.php" class="active">Menu</a>
      <a href="index.php#reservation">Reservations</a>
      <a href="index.php#lookup">My Reservation</a>
      <a href="index.php#about">About</a>
      <a href="index.php#contact">Contact</a>
    </nav>
  </div>
</header>

<main>
  <section class="menu-section">
    <div class="container">
      <div class="section-heading">
        <span class="section-label">Menu</span>
        <h2>Browse our menu</h2>
      </div>

      <div class="menu-toolbar">
        <button class="filter-pill active" data-filter="all">All</button>
        <button class="filter-pill" data-filter="appetizer">Appetizers</button>
        <button class="filter-pill" data-filter="main">Main Courses</button>
        <button class="filter-pill" data-filter="dessert">Desserts</button>
        <button class="filter-pill" data-filter="drinks">Drinks</button>
      </div>

      <div id="menuList" class="menu-grid">
        <?php foreach ($menuItems as $item): ?>
          <?php $filterSlug = menu_filter_slug($item['category']); ?>
          <?php $menuImage = $menuImages[strtolower($item['name'])] ?? 'images/springSalad.jpg'; ?>

          <article class="menu-card" data-category="<?= e($filterSlug) ?>">
            <img src="<?= e($menuImage) ?>" alt="<?= e($item['name']) ?>" class="menu-card-image">
            <div class="menu-card-content">
              <div class="menu-meta">
                <span class="category-chip"><?= e(menu_category_label($item['category'])) ?></span>
                <span class="badge <?= $item['availability_status'] === 'available' ? 'badge-confirmed' : 'badge-cancelled' ?>">
                  <?= e(ucfirst($item['availability_status'])) ?>
                </span>
              </div>
              <h3><?= e($item['name']) ?></h3>
              <p><?= e($item['description']) ?></p>
              <strong>$<?= e(number_format((float)$item['price'], 2)) ?></strong>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<script src="script.js"></script>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$flash = consume_flash();
$lookupEmail = trim($_GET['lookup_email'] ?? '');

$menuStmt = db()->query(
  'SELECT menu_item_id, name, description, price, category, availability_status
   FROM menu_items
   ORDER BY category, price, name'
);
$menuItems = $menuStmt->fetchAll();

$featuredItems = array_slice($menuItems, 0, 3);

$displayPhotos = [
  'images/restaurant.jpg',
  'images/restaurant.jpg',
  'images/restaurant.jpg',
];

$featuredImages = [
  'images/springSalad.jpg',
  'images/darkChoc.jpg',
  'images/braisedSalmon.jpg',
];

$lookupResults = [];
if ($lookupEmail !== '' && filter_var($lookupEmail, FILTER_VALIDATE_EMAIL)) {
  $lookupStmt = db()->prepare(
    'SELECT r.reservation_id, r.reservation_date, r.reservation_time, r.guests, r.status, r.special_request,
            rt.table_name, c.name, c.email
     FROM reservations r
     JOIN customers c ON c.customer_id = r.customer_id
     LEFT JOIN restaurant_tables rt ON rt.table_id = r.table_id
     WHERE c.email = :email
     ORDER BY r.reservation_date DESC, r.reservation_time DESC'
  );
  $lookupStmt->execute([':email' => $lookupEmail]);
  $lookupResults = $lookupStmt->fetchAll();
}

function reservation_status_badge(string $status): string {
  return match ($status) {
    'confirmed' => 'badge-confirmed',
    'cancelled' => 'badge-cancelled',
    default => 'badge-pending',
  };
}

function e(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Harvest Bistro — Reservations & Menu Management</title>
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
      <a href="#home" class="brand">
        <div class="logo">HB</div>
        <div class="brand-copy">
          <h1>Harvest Bistro</h1>
          <p class="tagline">Seasonal dining, seamless reservations, smarter restaurant management</p>
        </div>
      </a>

      <nav class="main-nav">
        <a href="#featured">Featured</a>
        <a href="menu.php">Menu</a>
        <a href="#reservation">Reservations</a>
        <a href="#lookup">My Reservation</a>
        <a href="#about">About</a>
        <a href="#contact">Contact</a>
      </nav>

      <a href="#reservation" class="nav-cta">Book a Table</a>
    </div>
  </header>

  <main>
    <section id="home" class="hero">
      <div class="container hero-grid">
        <div class="hero-content">
          <span class="eyebrow">Restaurant Reservation & Viewable Menu for Bistro</span>
          <h2>Beautiful dining experience.</h2>
          <p class="hero-text">
            Harvest Bistro provides elegant restaurant presentation. Customers can explore dishes, reserve tables online, and
            enjoy a smooth dining experience.
          </p>

          <div class="hero-actions">
            <a href="menu.php" class="btn btn-primary">Explore Menu</a>
            <a href="#reservation" class="btn btn-secondary">Reserve Now</a>
          </div>

          <div class="hero-highlights">
            <div class="highlight-card">
              <strong>Fresh Menu</strong>
              <span>Seasonal dishes and chef specials</span>
            </div>
            <div class="highlight-card">
              <strong>Easy Reservations</strong>
              <span>Book your reservation in minutes.</span>
            </div>
          </div>
        </div>

        <div class="hero-visual">
          <div class="hero-image-card large-image">
            <img src="<?= e($displayPhotos[0]) ?>" alt="Restaurant interior" />
          </div>
          <div class="hero-image-row">
            <div class="hero-image-card small-image">
              <img src="<?= e($displayPhotos[1]) ?>" alt="Dish" />
            </div>
            <div class="hero-image-card small-image">
              <img src="<?= e($displayPhotos[2]) ?>" alt="Table setup" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <?php if ($flash): ?>
      <div class="container flash-wrap">
        <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
      </div>
    <?php endif; ?>

    <section id="featured" class="featured-section">
      <div class="container">
        <div class="section-heading">
          <span class="section-label">Featured Favorites</span>
          <h2>Signature dishes guests keep coming back for</h2>
        </div>

        <div class="featured-grid">
          <?php foreach ($featuredItems as $index => $item): ?>
            <article class="featured-card">
              <img src="<?= e($featuredImages[$index]) ?>" alt="<?= e($item['name']) ?>" />
              <div class="featured-card-content">
                <span class="badge badge-featured">
                  <?= e($index === 0 ? 'Chef Pick' : ($index === 1 ? 'Popular' : 'Guest Favorite')) ?>
                </span>
                <h3><?= e($item['name']) ?></h3>
                <p><?= e($item['description']) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ⭐ MENU SECTION REMOVED COMPLETELY ⭐ -->

    <section id="reservation" class="reservation-section">
      <div class="container reservation-grid">
        <div class="reservation-copy">
          <span class="section-label">Reservations</span>
          <h2>Reserve your table in seconds</h2>
          <p>Submit your information for a fast, easy, and free booking.</p>

          <div class="reservation-info-cards">
            <div class="info-card">
              <h3>Dining Hours</h3>
              <p>Monday - Sunday</p>
              <p>11:00 AM - 10:00 PM</p>
            </div>

            <div class="info-card">
              <h3>Reservation Notes</h3>
              <p>For parties over 8, please contact us directly.</p>
              <p>Walk-ins welcome based on availability.</p>
            </div>

            <div class="info-card">
              <h3>Why Book Online?</h3>
              <p>Fast and secure confirmation.</p>
            </div>
          </div>
        </div>

        <div class="reservation-form-card">
          <h3>Make a Reservation</h3>
          <form id="reservationForm" method="post" action="reserve.php">
            <div class="form-grid">
              <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required />
              </div>

              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required />
              </div>

              <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required />
              </div>

              <div class="form-group">
                <label for="guests">Guests</label>
                <input type="number" id="guests" name="guests" min="1" required />
              </div>

              <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required />
              </div>

              <div class="form-group">
                <label for="time">Time</label>
                <input type="time" id="time" name="time" required />
              </div>

              <div class="form-group full-width">
                <label for="special_request">Special Request</label>
                <input type="text" id="special_request" name="special_request" />
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary full-btn">Reserve a Table</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section id="lookup" class="lookup-section">
      <div class="container">
        <div class="section-heading">
          <span class="section-label">Manage Reservation</span>
          <h2>View or cancel your reservation</h2>
        </div>

        <div class="lookup-shell">
          <form method="get" class="lookup-form">
            <label for="lookup_email">
              <span>Email</span>
              <input id="lookup_email" type="email" name="lookup_email" value="<?= e($lookupEmail) ?>" required />
            </label>
            <button type="submit">Find Reservations</button>
          </form>

          <?php if ($lookupEmail !== '' && $lookupResults === []): ?>
            <p class="empty-state">No reservations found for that email.</p>
          <?php endif; ?>

          <?php if ($lookupResults !== []): ?>
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Guests</th>
                    <th>Table</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lookupResults as $reservation): ?>
                    <tr>
                      <td>#<?= e((string)$reservation['reservation_id']) ?></td>
                      <td><?= e($reservation['reservation_date']) ?></td>
                      <td><?= e(substr($reservation['reservation_time'], 0, 5)) ?></td>
                      <td><?= e((string)$reservation['guests']) ?></td>
                      <td><?= e($reservation['table_name'] ?? 'Pending') ?></td>
                      <td><span class="badge <?= reservation_status_badge($reservation['status']) ?>"><?= e(ucfirst($reservation['status'])) ?></span></td>
                      <td>
                        <?php if ($reservation['status'] !== 'cancelled'): ?>
                          <form method="post" action="cancel_reservation.php">
                            <input type="hidden" name="reservation_id" value="<?= e((string)$reservation['reservation_id']) ?>" />
                            <input type="hidden" name="email" value="<?= e($lookupEmail) ?>" />
                            <button type="submit" class="danger-button">Cancel</button>
                          </form>
                        <?php else: ?>
                          <span class="muted-text">Already cancelled</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section id="about" class="about-section">
      <div class="container about-grid">
        <div class="about-image">
          <img src="https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=1200&q=80" alt="Restaurant table" />
        </div>

        <div class="about-content">
          <span class="section-label">About Harvest Bistro</span>
          <h2>A restaurant concept designed for both guests and management</h2>
          <p>Harvest Bistro is a modern restaurant located in Orlando, Florida.</p>

          <div class="about-points">
            <div class="about-point">
              <strong>Customer Experience</strong>
              <p>Simple menu browsing and reservation booking.</p>
            </div>

            <div class="about-point">
              <strong>Admin Controls</strong>
              <p>Staff dashboards available after login.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="contact" class="contact-section">
      <div class="container">
        <div class="section-heading">
          <span class="section-label">Visit Us</span>
          <h2>Location, hours, and contact</h2>
        </div>

        <div class="contact-grid">
          <article class="contact-card">
            <h3>Address</h3>
            <p>123 Main St</p>
            <p>Orlando, FL 32801</p>
          </article>

          <article class="contact-card">
            <h3>Hours</h3>
            <p>Mon - Sun</p>
            <p>11:00 AM - 10:00 PM</p>
          </article>

          <article class="contact-card">
            <h3>Contact</h3>
            <p><a href="tel:+1234567890">+1 (234) 567-890</a></p>
            <p><a href="mailto:hello@harvestbistro.com">hello@harvestbistro.com</a></p>
          </article>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <div class="logo">HB</div>
        <div>
          <h3>Harvest Bistro</h3>
          <p>Seasonal dining with smart reservation and menu management.</p>
        </div>
      </div>

      <div class="footer-links">
        <h4>Quick Links</h4>
        <a href="#featured">Featured</a>
        <a href="menu.php">Menu</a>
        <a href="#reservation">Reservations</a>
        <a href="#lookup">My Reservation</a>
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
        <span class="footer-note">Staff dashboards available after login.</span>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container">
        <small>&copy; <span id="year"></span> Harvest Bistro</small>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>

<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'restaurant_db';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
  static $pdo = null;

  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $pdo = new PDO(
    sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME),
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  return $pdo;
}

function redirect(string $path): void {
  header('Location: ' . $path);
  exit;
}

function e(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function flash(string $type, string $message): void {
  $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array {
  $flash = $_SESSION['flash'] ?? null;
  unset($_SESSION['flash']);
  return $flash;
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function require_role(array $roles): array {
  $user = current_user();
  if (!$user || !in_array($user['role'], $roles, true)) {
    flash('error', 'Please sign in with the required role.');
    redirect('login.php');
  }

  return $user;
}

function reservation_status_badge(string $status): string {
  return match ($status) {
    'confirmed' => 'badge-confirmed',
    'cancelled' => 'badge-cancelled',
    default => 'badge-pending',
  };
}

function selected(string $value, string $current): string {
  return $value === $current ? 'selected' : '';
}

<?php
/**
 * config.php — Database connection and global constants.
 * Include this file at the top of every PHP page.
 *
 * Adjust DB_HOST, DB_NAME, DB_USER, DB_PASS to match your environment.
 */

declare(strict_types=1);

// ── Database credentials ─────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'shopzone');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ── Site constants ────────────────────────────────────────────
define('SITE_NAME', 'ShopZone');
define('SITE_URL',  'http://localhost/php');   // no trailing slash
define('UPLOADS_DIR', __DIR__ . '/uploads/');  // absolute path
define('UPLOADS_URL', SITE_URL . '/uploads/'); // web-accessible URL

// ── Session start (idempotent) ────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── PDO connection (singleton) ────────────────────────────────
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose credentials in production — log instead
            error_log('DB connection failed: ' . $e->getMessage());
            die('<p style="font-family:sans-serif;color:#c0392b;padding:40px;">
                  Database connection failed. Check your credentials in config.php.
                 </p>');
        }
    }
    return $pdo;
}

// ── Helper: sanitise output ───────────────────────────────────
function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ── Helper: format price ──────────────────────────────────────
function price(mixed $amount): string
{
    return '$' . number_format((float)$amount, 2);
}

// ── Helper: cart item count (for nav badge) ───────────────────
function cartCount(): int
{
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}

// ── Helper: flash messages ────────────────────────────────────
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// ── Helper: redirect ──────────────────────────────────────────
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// ── Helper: admin guard ───────────────────────────────────────
function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

// ── Helper: slugify a string ──────────────────────────────────
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

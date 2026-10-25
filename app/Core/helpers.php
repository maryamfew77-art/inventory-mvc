<?php
// Safe output (stops XSS). Use it on every value you print in a view
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Builds a full address: url('products') -> http://localhost/inventory-mvc/public/products
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

// One secret token per session, used by every POST form (CSRF protection)
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// The hidden input every POST form needs
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function is_admin(): bool
{
    return ($_SESSION['user']['role'] ?? '') === 'admin';
}
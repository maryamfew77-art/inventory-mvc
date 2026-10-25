<?php
// Base controller: helper methods every controller can use
class Controller
{
    // Shows a view inside the header and footer layout
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);   // ['title' => 'Hi'] becomes the variable $title

        $file = ROOT . '/app/Views/' . $view . '.php';
        if (!is_file($file)) {
            http_response_code(500);
            die(APP_DEBUG ? "View not found: " . htmlspecialchars($view) : "Something went wrong.");
        }

        require ROOT . '/app/Views/layout/header.php';
        require $file;
        require ROOT . '/app/Views/layout/footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;   // always exit after a redirect
    }

    // Flash message: shown once on the next page ($type: 'success' or 'error')
    protected function flash(string $msg, string $type = 'success'): void
    {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }

    // Stops the request if the CSRF token is missing or wrong
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }

    protected function requireLogin(): void
    {
        if (!is_logged_in()) {
            $this->flash('Please log in first.', 'error');
            $this->redirect('auth/login');
        }
    }

    // Server-side role check: hiding a button is NOT enough
    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if (!is_admin()) {
            $this->flash('Only an admin can do that.', 'error');
            $this->redirect('dashboard');
        }
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // Reads one POST field as trimmed text ("" if missing or an array)
    protected function input(string $key): string
    {
        $v = $_POST[$key] ?? '';
        return is_string($v) ? trim($v) : '';
    }
}
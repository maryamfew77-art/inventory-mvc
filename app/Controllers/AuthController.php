<?php
class AuthController extends Controller
{
    // Address: /auth/login
    public function login(): void
    {
        if (is_logged_in()) {
            $this->redirect('dashboard');
        }

        $errors = [];
        $email  = '';

        if ($this->isPost()) {
            $this->verifyCsrf();
            $email = strtolower($this->input('email'));
            $pass  = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

            // Brute-force guard: after 5 wrong tries, wait 1 minute
            if (($_SESSION['fails'] ?? 0) >= 5 && time() - ($_SESSION['last_fail'] ?? 0) < 60) {
                $errors[] = 'Too many attempts. Please try again in 1 minute.';
            } else {
                $user = (new User())->findByEmail($email);

                // password_verify compares the typed password with the saved hash
                if ($user && password_verify($pass, $user['password'])) {
                    session_regenerate_id(true);   // new session ID after login (stops session fixation)
                    $_SESSION['user'] = [
                        'id'   => (int)$user['id'],
                        'name' => $user['name'],
                        'role' => $user['role'],
                    ];
                    unset($_SESSION['fails'], $_SESSION['last_fail']);
                    $this->flash('Welcome back, ' . $user['name'] . '!');
                    $this->redirect('dashboard');
                }

                $_SESSION['fails']     = ($_SESSION['fails'] ?? 0) + 1;
                $_SESSION['last_fail'] = time();
                $errors[] = 'Email or password is wrong.';   // same message for both, on purpose
            }
        }

        $this->view('auth/login', ['title' => 'Login', 'errors' => $errors, 'email' => $email]);
    }

    // Address: /auth/register
    public function register(): void
    {
        if (is_logged_in()) {
            $this->redirect('dashboard');
        }

        $errors = [];
        $old    = ['name' => '', 'email' => ''];   // sticky values (passwords are never refilled)

        if ($this->isPost()) {
            $this->verifyCsrf();
            $old['name']  = $this->input('name');
            $old['email'] = strtolower($this->input('email'));
            $pass    = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $confirm = is_string($_POST['confirm'] ?? null) ? $_POST['confirm'] : '';
            $users   = new User();

            if (mb_strlen($old['name']) < 3 || mb_strlen($old['name']) > 100) {
                $errors['name'] = 'Name must be between 3 and 100 characters.';
            }

            if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($old['email']) > 150) {
                $errors['email'] = 'Please enter a valid email address.';
            } elseif ($users->findByEmail($old['email'])) {
                $errors['email'] = 'This email is already registered.';
            }

            if (strlen($pass) < 8) {
                $errors['password'] = 'Password must be at least 8 characters.';
            } elseif (strlen($pass) > 72) {   // the hashing method only reads the first 72 characters
                $errors['password'] = 'Password must be 72 characters or less.';
            }

            if ($pass !== $confirm) {
                $errors['confirm'] = 'Passwords do not match.';
            }

            if (!$errors) {
                try {
                    $users->create($old['name'], $old['email'], $pass);
                    $this->flash('Account created! Please log in.');
                    $this->redirect('auth/login');
                } catch (PDOException $e) {   // two people registering the same email at once
                    $errors['email'] = 'This email is already registered.';
                }
            }
        }

        $this->view('auth/register', ['title' => 'Register', 'errors' => $errors, 'old' => $old]);
    }

    // Address: /auth/logout
    public function logout(): void
    {
        $_SESSION = [];       // empty all session data
        session_destroy();    // end the session on the server
        session_start();      // a fresh session, so we can show a flash message
        $this->flash('You have been logged out.');
        $this->redirect('auth/login');
    }
}
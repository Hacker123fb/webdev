<?php
/**
 * Login Controller & View
 * Semester V Web Development Mini Project
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users to their dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'student') {
        header("Location: student/dashboard.php");
        exit;
    }
}

// Include database connection
require_once __DIR__ . '/config/database.php';

$error_message = '';
$success_message = '';
$username_input = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success_message = "You have been logged out successfully.";
}

// Handle Login Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (empty($username_input) || empty($password_input)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password_input, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user_id']  = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                    exit;
                } else {
                    header("Location: student/dashboard.php");
                    exit;
                }
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error_message = "A database error occurred. Please try again later.";
        }
    }
}

$page_title = "Login - Library Management System";
$base_path = "";
require_once __DIR__ . '/includes/header.php';
?>

<main class="main-content">
    <div class="container" style="max-width: 420px;">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto 0.75rem auto;">L</div>
                <h1 class="auth-title">User Login</h1>
                <p class="auth-subtitle">Sign in to your library account</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($username_input); ?>" 
                        required 
                        autocomplete="username"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        required 
                        autocomplete="current-password"
                    >
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </div>
            </form>

            <div class="test-creds-box">
                <p class="test-creds-title"><strong>Default Test Credentials:</strong></p>
                <p>&bull; <strong>Admin:</strong> <code>admin</code> / <code>admin123</code></p>
                <p>&bull; <strong>Student:</strong> <code>student</code> / <code>student123</code></p>
            </div>
        </div>
    </div>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

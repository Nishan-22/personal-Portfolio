<?php
include("../config/db_connect.php");
session_start();

// Handle POST login securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // simple CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        if ($email === '' || $password === '') {
            $error = 'Please fill in both email and password.';
        } else {
            // Prepared statement to fetch user by email
            $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);
                $dbpass = $user['password'];
                $password_ok = false;

                // If stored with password_hash() (bcrypt/argon2) use password_verify
                if (strpos($dbpass, '$2y$') === 0 || strpos($dbpass, '$argon2') === 0 || password_get_info($dbpass)['algo'] !== 0) {
                    if (password_verify($password, $dbpass)) {
                        $password_ok = true;
                    }
                } else {
                    // fallback: maybe stored as md5 (32 chars)
                    if (md5($password) === $dbpass) {
                        $password_ok = true;
                        // upgrade to password_hash
                        $newhash = password_hash($password, PASSWORD_DEFAULT);
                        $up = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
                        mysqli_stmt_bind_param($up, 'si', $newhash, $user['id']);
                        mysqli_stmt_execute($up);
                    }
                }

                if ($password_ok) {
                    // successful login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect all users to the home page after login. If you later
                    // want admins to go to an admin dashboard, change this target.
                    header('Location: ../index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password!';
                }
            } else {
                $error = 'Invalid email or password!';
            }
        }
    }
}

// ensure a CSRF token exists for the form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-2xl shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">🔐 Login</h2>
    <?php if (!empty($error)): ?>
    <div class="mb-4 text-sm text-red-600 bg-red-100 p-2 rounded"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <div>
            <label class="block mb-2 font-semibold">Email</label>
            <input type="email" name="email" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label class="block mb-2 font-semibold">Password</label>
            <input type="password" name="password" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <button type="submit" name="login"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 rounded-lg transition duration-200">
            Login
        </button>
        </form>
    </div>
    </body>
    </html>

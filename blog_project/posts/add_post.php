
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
  header("Location: ../auth/login.php");
  exit();
}

include("../config/db_connect.php");

// Ensure a CSRF token exists
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  // Basic CSRF check
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $error = 'Invalid request. Please try again.';
  } else {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
      $error = 'Please fill in both title and content.';
    } else {
      // Prepared statement to prevent SQL injection
      $stmt = mysqli_prepare($conn, "INSERT INTO posts (title, content, created_at) VALUES (?, ?, NOW())");
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $title, $content);
        if (mysqli_stmt_execute($stmt)) {
          // Invalidate token to prevent double submissions
          unset($_SESSION['csrf_token']);
          // Set a flash message for the dashboard
          $_SESSION['flash'] = 'Post added successfully!';
          header('Location: ../admin/dashboard.php');
          exit();
        } else {
          $error = 'Database error: ' . mysqli_error($conn);
        }
      } else {
        $error = 'Database error: ' . mysqli_error($conn);
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Post</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

  <div class="max-w-xl mx-auto mt-20 bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-blue-600">✍️ Create New Blog Post</h2>

    <?php if (!empty($error)): ?>
      <div class="mb-4 text-sm text-red-600 bg-red-100 p-2 rounded"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <div>
        <label class="block text-gray-700 font-semibold mb-2">Title</label>
        <input type="text" name="title" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-2">Content</label>
        <textarea name="content" rows="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none" required></textarea>
      </div>

      <div class="flex justify-between items-center">
        <a href="../admin/dashboard.php" class="text-gray-500 hover:text-blue-600">← Back</a>
        <button type="submit" name="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">Publish</button>
      </div>
    </form>
  </div>

</body>
</html>

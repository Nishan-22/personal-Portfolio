<?php
// Admin dashboard - lists posts with edit/delete actions
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

include __DIR__ . '/../config/db_connect.php';

// ensure CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
$error = '';

// Handle delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf) {
        $error = 'Invalid request.';
    } else {
        $del_id = intval($_POST['delete_id']);
        if ($del_id > 0) {
            $dstmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
            mysqli_stmt_bind_param($dstmt, 'i', $del_id);
            if (mysqli_stmt_execute($dstmt)) {
                $_SESSION['flash'] = 'Post deleted.';
                // regenerate token to prevent replay
                unset($_SESSION['csrf_token']);
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Database error: ' . mysqli_error($conn);
            }
        }
    }
}

// Fetch posts
$stmt = mysqli_prepare($conn, "SELECT id, title, created_at FROM posts ORDER BY created_at DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../style.css">
</head>
<body class="min-h-screen">
  <nav class="bg-white shadow p-4">
    <div class="max-w-5xl mx-auto flex justify-between items-center px-4">
      <div class="text-xl font-bold">Admin Dashboard</div>
      <div>
        <a href="../index.php" class="text-gray-700 hover:text-blue-600 mr-4">Home</a>
        <a href="../auth/logout.php" class="text-red-600">Logout</a>
      </div>
    </div>
  </nav>

  <main class="max-w-5xl mx-auto p-4 mt-8">
    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($_SESSION['flash']); ?></div>
      <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-semibold">Posts</h1>
      <a href="../posts/add_post.php" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Post</a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($row['title']); ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('F j, Y', strtotime($row['created_at'])); ?></td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="../posts/edit_post.php?id=<?= urlencode($row['id']); ?>" class="text-yellow-600 mr-4">Edit</a>
                <form method="POST" class="inline" onsubmit="return confirm('Delete this post?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf); ?>">
                  <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['id']); ?>">
                  <button type="submit" class="text-red-600">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3" class="px-6 py-4 text-sm text-gray-500">No posts found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>

<?php
session_start();
// only admin allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

include __DIR__ . '/../config/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: ../index.php');
    exit;
}

// ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Load post data
$stmt = mysqli_prepare($conn, "SELECT id, title, content FROM posts WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: ../index.php');
    exit;
}
$post = mysqli_fetch_assoc($result);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        if (isset($_POST['delete'])) {
            // delete post
            $dstmt = mysqli_prepare($conn, "DELETE FROM posts WHERE id = ?");
            mysqli_stmt_bind_param($dstmt, 'i', $id);
            if (mysqli_stmt_execute($dstmt)) {
                $_SESSION['flash'] = 'Post deleted.';
                // invalidate token
                unset($_SESSION['csrf_token']);
                header('Location: ../index.php');
                exit;
            } else {
                $error = 'Database error: ' . mysqli_error($conn);
            }
        } else {
            // update
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            if ($title === '' || $content === '') {
                $error = 'Title and content are required.';
            } else {
                $ustmt = mysqli_prepare($conn, "UPDATE posts SET title = ?, content = ? WHERE id = ?");
                mysqli_stmt_bind_param($ustmt, 'ssi', $title, $content, $id);
                if (mysqli_stmt_execute($ustmt)) {
                    $_SESSION['flash'] = 'Post updated.';
                    unset($_SESSION['csrf_token']);
                    header('Location: view_post.php?id=' . urlencode($id));
                    exit;
                } else {
                    $error = 'Database error: ' . mysqli_error($conn);
                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Post</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-xl mx-auto mt-20 bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold mb-6 text-blue-600">✏️ Edit Post</h2>

    <?php if ($error): ?>
      <div class="mb-4 text-sm text-red-600 bg-red-100 p-2 rounded"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token); ?>">
      <div>
        <label class="block text-gray-700 font-semibold mb-2">Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($post['title']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2" required>
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-2">Content</label>
        <textarea name="content" rows="8" class="w-full border border-gray-300 rounded-lg px-4 py-2" required><?= htmlspecialchars($post['content']); ?></textarea>
      </div>

      <div class="flex justify-between items-center">
        <a href="view_post.php?id=<?= urlencode($post['id']); ?>" class="text-gray-500 hover:text-blue-600">← Back</a>
        <div class="flex gap-2">
          <button type="submit" name="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg">Save</button>
          <button type="submit" name="delete" onclick="return confirm('Delete this post?');" class="bg-red-500 text-white px-4 py-2 rounded-lg">Delete</button>
        </div>
      </div>
    </form>
  </div>
</body>
</html>

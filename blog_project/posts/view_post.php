<?php
include(__DIR__ . '/../config/db_connect.php');
// allow checking session for admin link
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Allow public viewing of a single post by id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
  header('Location: ../index.php');
  exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, title, content, created_at FROM posts WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
  // Post not found
  header('Location: ../index.php');
  exit;
}

$post = mysqli_fetch_assoc($result);

// Helper: convert plaintext URLs to safe clickable links while keeping other
// text escaped. This avoids allowing arbitrary HTML from post content.
function auto_link_urls(string $text): string {
  // Escape the text first to neutralize any HTML
  $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

  // Regex to find http/https URLs. This operates on the escaped text which
  // preserves URL characters while preventing injection of tags.
  $pattern = '/\b(https?:\/\/[^\s"<>]+)\b/i';

  $linked = preg_replace_callback($pattern, function ($m) {
    $url = $m[1];
    $href = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    // Use target=_blank and rel attributes for safety
    return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer">' . $href . '</a>';
  }, $escaped);

  return $linked;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($post['title']); ?> - My Blog</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

  <nav class="bg-blue-600 text-white px-8 py-4 flex justify-between items-center shadow-md">
    <h1 class="text-2xl font-bold">Projects</h1>
    <a href="/blog_project/index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-100">Home</a>
  </nav>

  <div class="max-w-3xl mx-auto mt-10 px-4">
    <article class="bg-white p-6 rounded-xl shadow-md mb-6 border border-gray-100">
      <h1 class="text-3xl font-bold text-blue-700 mb-4"><?= htmlspecialchars($post['title']); ?></h1>
      <div class="text-sm text-gray-500 mb-6">Published: <?= date("F j, Y, g:i a", strtotime($post['created_at'])); ?></div>
      <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <div class="mb-4">
          <a href="edit_post.php?id=<?= urlencode($post['id']); ?>" class="text-sm bg-yellow-100 text-yellow-800 px-3 py-1 rounded">Edit post</a>
        </div>
      <?php endif; ?>
      <div class="prose max-w-none text-gray-800 break-words whitespace-pre-wrap">
        <?= nl2br(auto_link_urls($post['content'])); ?>
      </div>
    </article>
  </div>

</body>
</html>

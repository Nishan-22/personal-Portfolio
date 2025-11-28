<?php
// Backup of original blog index — kept as index_blog.php when converting to a personal site
include("config/db_connect.php");
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blog - backup</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{background:#f8fafc}</style>
</head>
<body class="bg-gray-50 text-gray-800">
  <nav class="bg-blue-600 text-white px-8 py-4 flex justify-between items-center shadow-md">
    <h1 class="text-2xl font-bold"> Projects</h1>
    <a href="/blog_project/index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-100">Home</a>
  </nav>

  <div class="max-w-3xl mx-auto mt-10 px-4">
    <h2 class="text-3xl font-semibold mb-6">Recent projects</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="bg-white p-6 rounded-xl shadow-md mb-6 border border-gray-100 hover:shadow-lg transition">
          <h3 class="text-2xl font-semibold text-blue-700 mb-2"><?= htmlspecialchars($row['title']); ?></h3>
          <p class="text-gray-700 mb-3"><?= nl2br(htmlspecialchars(substr($row['content'], 0, 200))); ?>...</p>
          <div class="flex justify-between text-sm text-gray-500">
            <span>🕓 <?= date("F j, Y, g:i a", strtotime($row['created_at'])); ?></span>
            <a href="posts/view_post.php?id=<?= urlencode($row['id']); ?>" class="text-blue-500 hover:underline">Read more</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-gray-600">No posts yet.</p>
    <?php endif; ?>
  </div>

</body>
</html>

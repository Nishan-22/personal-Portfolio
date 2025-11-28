<?php
// Professional portfolio landing page
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$result = false;
if (file_exists(__DIR__ . '/config/db_connect.php')) {
  include_once __DIR__ . '/config/db_connect.php';
  if (!empty($conn)) {
    $sql = "SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC LIMIT 6";
    $res = @mysqli_query($conn, $sql);
    if ($res) {
      $result = $res;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sagar Paudel — Portfolio</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50 text-gray-800 leading-relaxed">

  <header class="bg-white shadow-sm">
    <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center text-white font-bold">SP</div>
        <div>
          <a href="/blog_project/" class="text-lg font-semibold text-gray-900">Sagar Paudel</a>
          <div class="text-sm text-gray-500">Civil Engineer — Structural Design & Project Management</div>
        </div>
      </div>
      <nav class="space-x-4 text-sm">
        <a href="#about" class="text-gray-600 hover:text-gray-900">About</a>
        <a href="#projects" class="text-gray-600 hover:text-gray-900">Projects</a>
        <a href="#contact" class="text-gray-600 hover:text-gray-900">Contact</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="admin/dashboard.php" class="text-gray-600 hover:text-gray-900">Dashboard</a>
          <a href="auth/logout.php" class="text-red-600">Logout</a>
        <?php else: ?>
          <a href="auth/login.php" class="text-blue-600">Admin</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="max-w-4xl mx-auto px-6 py-12">
    <!-- Hero -->
    <section id="hero" class="mb-12">
      <div class="bg-white p-8 rounded-lg shadow">
  <h1 class="text-3xl font-bold text-gray-900">Hello — I'm Sagar.</h1>
  <p class="mt-3 text-gray-600">I deliver practical, durable, and sustainable engineering solutions focused on structural design, site supervision, and project management. I ensure projects meet safety standards, efficiency goals, and client expectations.</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <a href="#projects" class="inline-block bg-blue-600 text-white px-4 py-2 rounded">View projects</a>
          <a href="#contact" class="inline-block border border-gray-300 px-4 py-2 rounded text-gray-700">Get in touch</a>
        </div>
      </div>
    </section>

    <!-- About + Skills -->
    <section id="about" class="mb-12 grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="md:col-span-2 bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-semibold mb-3">About me</h2>
        <p class="text-gray-700">I’m a Civil Engineer with over five years of professional experience in planning, designing, and managing construction projects. I enjoy creating practical, durable, and sustainable engineering solutions that balance innovation with real-world functionality.

I focus on structural design, site supervision, and project management, ensuring that every project meets safety standards, efficiency goals, and client expectations. I value precision, teamwork, and continuous learning, and I take pride in transforming ideas into reliable structures that stand the test of time.</p>
        <h3 class="text-lg font-semibold mt-4">Expertise</h3>
        <ul class="mt-2 text-sm text-gray-600 list-disc list-inside">
          <li>Design &amp; Planning: AutoCAD, STAAD Pro, Revit</li>
          <li>Project Management: Estimation, Scheduling, Quality Control</li>
          <li>Field Experience: Site Supervision, Surveying, Material Testing</li>
          <li>Tools: MS Project, Excel, AutoCAD, Total Station</li>
        </ul>
      </div>
      <aside class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-xl font-semibold mb-3">Skills</h3>
        <div class="flex flex-wrap gap-2">
          <span class="text-sm px-3 py-1 bg-gray-100 rounded">AutoCAD</span>
          <span class="text-sm px-3 py-1 bg-gray-100 rounded">STAAD Pro</span>
          <span class="text-sm px-3 py-1 bg-gray-100 rounded">Revit</span>
          <span class="text-sm px-3 py-1 bg-gray-100 rounded">MS Project</span>
          <span class="text-sm px-3 py-1 bg-gray-100 rounded">Total Station</span>
          <span class="text-sm px-3 py-1 bg-gray-100 rounded">Material Testing</span>
        </div>
      </aside>
    </section>

    <!-- Projects -->
    <section id="projects" class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Projects</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
              // Attempt to find a project image by id in assets/projects (jpg, png, webp)
              $img = null;
              $base = __DIR__ . '/assets/projects/' . $row['id'];
              foreach (['jpg','png','webp'] as $ext) {
                if (file_exists($base . '.' . $ext)) {
                  $img = 'assets/projects/' . $row['id'] . '.' . $ext;
                  break;
                }
              }
            ?>
            <article class="project-card bg-white p-5 rounded-lg shadow-sm">
              <?php if ($img): ?>
                <img src="<?= htmlspecialchars($img); ?>" alt="<?= htmlspecialchars($row['title']); ?>" class="w-full h-40 object-cover rounded-md mb-3">
              <?php endif; ?>
              <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($row['title']); ?></h3>
              <p class="text-gray-600 mb-3 text-sm"><?= nl2br(htmlspecialchars(substr($row['content'], 0, 180))); ?><?php if (strlen($row['content']) > 180) echo '...'; ?></p>
              <div class="text-sm">
                <a href="posts/view_post.php?id=<?= urlencode($row['id']); ?>" class="text-blue-600">Read more</a>
                <span class="text-gray-400 ml-3">Published: <?= date('M j, Y', strtotime($row['created_at'])); ?></span>
              </div>
            </article>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="bg-white p-6 rounded shadow text-gray-600">No projects yet — log in to the admin area and add your first project.</div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Contact -->
    <section id="contact" class="mb-12">
      <h2 class="text-2xl font-semibold mb-4">Contact</h2>
      <div class="bg-white p-6 rounded-lg shadow flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <p class="text-gray-700">Email: <a href="mailto:you@example.com" class="text-blue-600">you@example.com</a></p>
          <p class="text-gray-700">Facebook: <a href="https://www.facebook.com/officialsagarpaudel" target="_blank" rel="noopener noreferrer" class="text-blue-600">facebook.com/officialsagarpaudel</a></p>
        </div>
        <div class="flex items-center gap-3">
          <a href="#projects" class="text-sm border border-gray-300 px-3 py-1 rounded">See projects</a>
          <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="admin/dashboard.php" class="text-sm text-gray-700">Dashboard</a>
          <?php else: ?>
            <a href="auth/login.php" class="text-sm text-blue-600">Admin</a>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <footer class="border-t py-6">
    <div class="max-w-4xl mx-auto px-6 text-center text-sm text-gray-500">© <?= date('Y'); ?> Nishan Paudel — Built with PHP</div>
  </footer>

</body>
</html>

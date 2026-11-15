<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['visits'] = ($_SESSION['visits'] ?? 0) + 1;
}

require 'db.php';

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = trim($_POST['author'] ?? '');
    $body   = trim($_POST['body'] ?? '');

    if ($author !== '' && $body !== '') {
        $stmt = $pdo->prepare('INSERT INTO messages (author, body) VALUES (:a, :b)');
        $stmt->execute([':a' => $author, ':b' => $body]);

        if (isset($_POST['remember'])) {
            setcookie('author', $author, time() + 60*60*24*30, '/');  
        } else {
            setcookie('author', '', time() - 3600, '/');            
        }
    }
    header('Location: index.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE body ILIKE :q ORDER BY created_at DESC');
    $stmt->execute([':q' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM messages ORDER BY created_at DESC');
}
$messages = $stmt->fetchAll();

$savedName = $_COOKIE['author'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Class Wall</title>
  <style>
    * { box-sizing: border-box; }

    body {
      margin: 0;
      padding: 2rem 1rem;
      font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
      background: #f5f5f4;
      color: #1c1917;
      line-height: 1.5;
    }

    .wrap { max-width: 560px; margin: 0 auto; }

    header { margin-bottom: 1.5rem; }
    h1 { margin: 0; font-size: 1.75rem; }
    .visits { margin: 0.25rem 0 0; color: #78716c; font-size: 0.875rem; }

    .card {
      background: #fff;
      border: 1px solid #e7e5e4;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    input[type="text"], textarea {
      width: 100%;
      padding: 0.6rem 0.75rem;
      margin-bottom: 0.75rem;
      border: 1px solid #d6d3d1;
      border-radius: 6px;
      font: inherit;
    }
    textarea { min-height: 90px; resize: vertical; }
    input:focus, textarea:focus { outline: 2px solid #1c1917; outline-offset: 1px; }

    .remember { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; margin-bottom: 0.75rem; }

    button {
      padding: 0.55rem 1rem;
      border: 0;
      border-radius: 6px;
      background: #1c1917;
      color: #fff;
      font: inherit;
      cursor: pointer;
    }
    button:hover { background: #44403c; }

    .search { display: flex; gap: 0.5rem; align-items: center; }
    .search input { margin-bottom: 0; flex: 1; }
    .clear { font-size: 0.875rem; color: #78716c; }

    .message .meta { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.875rem; }
    .message .time { color: #78716c; }
    .message p { margin: 0.4rem 0 0; white-space: pre-wrap; overflow-wrap: anywhere; }

    .empty { text-align: center; color: #78716c; }
  </style>
</head>
<body>
  <div class="wrap">

    <header>
      <h1>Class Wall</h1>
      <p class="visits">You've visited <?= (int)($_SESSION['visits'] ?? 1) ?> times.</p>
    </header>

    <form method="POST" class="card">
      <input type="text" name="author" value="<?= h($savedName) ?>" placeholder="Your name" maxlength="50" required>
      <textarea name="body" placeholder="Write a message..." required></textarea>
      <label class="remember">
        <input type="checkbox" name="remember" <?= $savedName !== '' ? 'checked' : '' ?>>
        Remember my name
      </label>
      <button type="submit">Post</button>
    </form>

    <form method="GET" class="card search">
      <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search messages">
      <button type="submit">Search</button>
      <?php if ($search !== ''): ?>
        <a href="index.php" class="clear">Clear</a>
      <?php endif; ?>
    </form>

    <?php if (empty($messages)): ?>
      <p class="empty">No messages found.</p>
    <?php endif; ?>

    <?php foreach ($messages as $m): ?>
      <div class="card message">
        <div class="meta">
          <strong><?= h($m['author']) ?></strong>
          <span class="time"><?= h($m['created_at']) ?></span>
        </div>
        <p><?= h($m['body']) ?></p>
      </div>
    <?php endforeach; ?>

  </div>
</body>
</html>
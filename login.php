<?php
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $users = query('SELECT * FROM users WHERE username = ?', $_POST['username']);
    $user = $users[0] ?? null;

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        // hash on new
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            query('UPDATE users SET password_hash = ? WHERE user_id = ?', $hash, $user['user_id']);
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        header('Location: index.php');
        exit;
    }

    $error = "That username and password don't match.";
}

$title = 'Sign in';
$page = 'login';
require 'header.php';
?>

<div class="content">
  <h1>Welcome back, spud.</h1>
  <p class="meta">Sign in to keep your streak growing.</p>

  <form method="post">
    <p class="error"><?= $error ?></p>
    <label class="label">
      Username
      <input name="username" placeholder="abc1234">
    </label>
    <label class="label">
      Password
      <input name="password" type="password">
    </label>
    <button class="btn dark">Sign in</button>
  </form>

  <p class="meta">This is a POTATO password, not your RIT one. Don't use your RIT password here.</p>

  <?= potato([], 147, 'class="peek"') ?>
</div>

<?php require 'footer.php'; ?>
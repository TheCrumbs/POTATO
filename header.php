<?php
// same nav and sidebar
// Pages set $title and $page before including
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/potato.php';

// reward amounts and costs, ie $settings['lesson_reward']
$settings = array_column(query('SELECT name, value FROM settings'), 'value', 'name');

// the signed in user, or null for guests or if something catastrophically went wrong
$me = null;
if (isset($_SESSION['user_id'])) {
    $me = query('SELECT * FROM users WHERE user_id = ?', $_SESSION['user_id'])[0];
    $equipped = equipped($me['user_id']);

    // your rank is the highest one your lifetime Primospuds have reached
    $rank = query('SELECT * FROM ranks WHERE min_lifetime <= ?
                   ORDER BY min_lifetime DESC LIMIT 1', $me['lifetime'])[0];

    // null if you're already at the top rank already
    $nextRank = query('SELECT * FROM ranks WHERE min_lifetime > ?
                       ORDER BY min_lifetime LIMIT 1', $me['lifetime'])[0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= $title ?> · POTATO</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $page ?>">

<header>
  <a class="logo" href="index.php"><?= potato([], 30) ?> POTATO</a>

  <?php if ($page != 'login'): ?>
    <nav>
      <a href="index.php" class="<?= $page == 'lessons' ? 'on' : '' ?>">Lessons</a>
      <a href="#">Reference</a>
      <a href="#">Daily Dig</a>
      <a href="#">Final Exam</a>
    </nav>
  <?php endif ?>

  <?php if ($me): ?>
    <span class="balance">
      <?= primospud() ?> <?= number_format($me['balance']) ?> Primospuds
      · <?= $me['streak'] ?>-day streak
    </span>
  <?php endif ?>
</header>

<?php if ($page != 'login'): ?>
<div class="sidebar">
  <?php if ($me): ?>
    <?= potato(array_column($equipped, 'name'), 87) ?>
    <h3><?= e($me['username']) ?></h3>

    <?php if (isset($equipped['story'])): ?>
      <p class="story"><?= $equipped['story']['name'] ?></p>
      <span class="grade g<?= $equipped['story']['grade_id'] ?>"><?= $equipped['story']['grade'] ?></span>
    <?php endif ?>

    <table>
      <tr><th>Rank</th><td><?= $rank['name'] ?></td></tr>
      <tr><th>Streak</th><td><?= $me['streak'] ?> days</td></tr>
      <tr>
        <th>Cold storage</th>
        <td>
          <?php for ($i = 1; $i <= $rank['cold_slots']; $i++): ?>
            <span class="cold <?= $i <= $me['cold_storage'] ? 'full' : '' ?>"></span>
          <?php endfor ?>
        </td>
      </tr>
    </table>

    <?php if ($nextRank):
        $earned = $me['lifetime'] - $rank['min_lifetime'];
        $needed = $nextRank['min_lifetime'] - $rank['min_lifetime'];
        $left = $nextRank['min_lifetime'] - $me['lifetime'];
    ?>
      <div class="bar"><div style="width: <?= 100 * $earned / $needed ?>%"></div></div>
      <p class="meta"><?= $left ?> Primospuds to <?= $nextRank['name'] ?></p>
    <?php endif ?>

    <hr>
    <a href="leaderboards.php" class="<?= $page == 'leaderboards' ? 'on' : '' ?>">Leaderboards</a>
    <a href="#">Tater Stand</a>
    <a href="#">The Sack</a>
    <a href="#" class="mono">~/.config</a>
    <a href="logout.php" class="signout">Sign out</a>

  <?php else: ?>
    <?= potato([], 87) ?>
    <h3>Hey, spud.</h3>
    <p class="meta">All the lessons are free. Sign in to earn Primospuds and keep a streak.</p>
    <a href="login.php" class="btn dark">Sign in</a>
  <?php endif ?>
</div>
<?php endif ?>

<main>
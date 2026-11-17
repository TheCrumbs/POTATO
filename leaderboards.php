<?php
// the top 10 spud people, sorted by lifetime Primospuds, current Primospuds, or streak.
$title = 'Leaderboards';
$page = 'leaderboards';
require 'header.php';

$tabs = [
    'lifetime' => 'Lifetime Primospuds',
    'balance' => 'Current Primospuds',
    'streak' => 'Streak',
];

// get the tabs and sort by default to lifetime depends on if get is gotten
$sort = $_GET['sort'] ?? 'lifetime'; // oooo fancy ternary im so fancy
if (!isset($tabs[$sort])) {
    $sort = 'lifetime';
}

// lifetime Primospuds btw
$players = query("SELECT users.*,
                    (SELECT name FROM ranks
                     WHERE min_lifetime <= users.lifetime
                     ORDER BY min_lifetime DESC LIMIT 1) AS rank_name
                  FROM users
                  ORDER BY $sort DESC
                  LIMIT 10");

// placement = how many people are ahead of you + plus one
if ($me) {
    $ahead = query("SELECT COUNT(*) AS n FROM users WHERE $sort > ?", $me[$sort]);
    $place = $ahead[0]['n'] + 1;
}
?>

<div class="content">
  <p class="path">~/leaderboards</p>
  <h1>Leaderboards</h1>
  <p class="meta">The top 10 spuds. Updates every time someone claims a reward.</p>

  <div class="tabs">
    <?php foreach ($tabs as $column => $name): ?>
      <a href="?sort=<?= $column ?>" class="<?= $column == $sort ? 'on' : '' ?>"><?= $name ?></a>
    <?php endforeach ?>
  </div>

  <table class="board">
    <tr>
      <th>#</th>
      <th>Spud</th>
      <th>Story</th>
      <th>Rank</th>
      <th style="text-align: right"><?= $tabs[$sort] ?></th>
    </tr>

    <?php foreach ($players as $i => $player):
        $items = equipped($player['user_id']);
        $isMe = $me && $player['user_id'] == $me['user_id'];
    ?>
      <tr class="<?= $isMe ? 'you' : '' ?>">
        <td class="place"><?= $i == 0 ? '✦' : $i + 1 ?></td>

        <td>
          <?= potato(array_column($items, 'name'), 36) ?>
          <?= e($player['username']) ?>
          <?php if ($isMe): ?>
            <span class="you-tag">YOU</span>
          <?php endif ?>
        </td>

        <td>
          <?php if (isset($items['story'])): ?>
            <span class="dot g<?= $items['story']['grade_id'] ?>"></span>
            <span class="story"><?= $items['story']['name'] ?></span>
          <?php endif ?>
        </td>

        <td><?= $player['rank_name'] ?></td>

        <td class="score">
          <?php if ($sort == 'streak'): ?>
            <?= $player['streak'] ?> days
          <?php else: ?>
            <?= primospud() ?> <?= number_format($player[$sort]) ?>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
  </table>

  <?php if ($me): ?>
    <p class="meta">You're #<?= $place ?> on this board.</p>
  <?php else: ?>
    <p class="meta"><a href="login.php">Sign in</a> to get on the board.</p>
  <?php endif ?>
</div>

<?php require 'footer.php'; ?>
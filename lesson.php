<?php
// step of a lesson, i.e. lesson.php?id=2&step=2&os=mac
require 'db.php';

$id = (int) ($_GET['id'] ?? 2);
$step = (int) ($_GET['step'] ?? 1);
$os = ($_GET['os'] ?? '') == 'mac' ? 'mac' : 'windows';

$lesson = query('SELECT lessons.*, topics.slug AS topic FROM lessons
                 JOIN topics ON topics.topic_id = lessons.topic_id
                 WHERE lesson_id = ?', $id)[0];

// claim button
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $reward = query("SELECT value FROM settings WHERE name = 'lesson_reward'")[0]['value'];

    $mysqli->begin_transaction();
    // INSERT IGNORE make it skip if they already claimed lesson, so no double pay
    $added = query('INSERT IGNORE INTO lesson_claims (user_id, lesson_id) VALUES (?, ?)', $userId, $id);
    if ($added == 1) {
        query('UPDATE users SET balance = balance + ?, lifetime = lifetime + ?
               WHERE user_id = ?', $reward, $reward, $userId);
    }
    $mysqli->commit();

    header("Location: lesson.php?id=$id&step=$step&os=$os");
    exit;
}

$title = $lesson['title'];
$page = 'lessons';
require 'header.php';

// assumes lessons/<topic>/<lesson>.php
$prompt = $os == 'mac' ? 'spud@laptop ~ %' : 'PS C:\Users\spud>';
$file = "lessons/{$lesson['topic']}/{$lesson['slug']}.php";
if (file_exists($file)) {
    $steps = require $file;
} else {
    $steps = [['name' => 'Coming soon', 'text' => "This lesson isn't written yet."]];
}

if (!isset($steps[$step - 1])) {
    $step = 1;
}
$current = $steps[$step - 1];
$isLastStep = $step == count($steps);

// lock until done, ones done arent locked
$hasTerminal = isset($current['terminal']);
$locked = $hasTerminal ? 'locked' : '';
$link = "lesson.php?id=$id&os=$os&step=";

$claimed = false;
if ($me) {
    $claimed = query('SELECT * FROM lesson_claims WHERE user_id = ? AND lesson_id = ?', $me['user_id'], $id);
}
$reward = $settings['lesson_reward'];
?>

<div class="content lesson">
  <div>
    <p class="path">~/lessons/<?= $lesson['topic'] ?>/<?= $lesson['slug'] ?></p>

    <div class="os">
      <a href="lesson.php?id=<?= $id ?>&step=<?= $step ?>&os=windows"
         class="<?= $os == 'windows' ? 'on' : '' ?>">Windows</a>
      <a href="lesson.php?id=<?= $id ?>&step=<?= $step ?>&os=mac"
         class="<?= $os == 'mac' ? 'on' : '' ?>">Mac</a>
    </div>

    <h1><?= $lesson['title'] ?></h1>
    <p class="meta"><?= $lesson['topic'] ?> · lesson <?= $lesson['sort'] ?> · +<?= $reward ?> Primospuds</p>

    <p class="meta">
      <?php for ($i = 1; $i <= count($steps); $i++): ?>
        <span class="square <?= $i < $step ? 'done' : '' ?> <?= $i == $step ? 'now' : '' ?>"></span>
      <?php endfor ?>
      Step <?= $step ?> of <?= count($steps) ?>
    </p>

    <h2><?= $current['name'] ?></h2>
    <p><?= $current['text'] ?></p>
    <p><?= $current['more'] ?? '' ?></p>

    <?php if ($hasTerminal): ?>
      <div class="terminal">
        <div class="title">
          <span><?= $os == 'mac' ? 'Terminal' : 'Windows PowerShell' ?></span>
          <span>TRY IT</span>
        </div>
        <pre></pre>
        <label>
          <span class="prompt"></span>
          <input autocomplete="off" spellcheck="false">
        </label>
      </div>
      <p class="hint"></p>
      <script id="script" type="application/json"><?= json_encode($current['terminal']) ?></script>
    <?php endif ?>

    <?php if (isset($current['tip'])): ?>
      <p class="box">✦ <?= $current['tip'] ?></p>
    <?php endif ?>

    <div class="buttons">
      <a class="btn" href="<?= $step > 1 ? $link . ($step - 1) : 'index.php' ?>">Back</a>

      <?php if ($isLastStep): ?>
        <a id="next" class="btn dark <?= $locked ?>" href="index.php">Finish lesson</a>
      <?php else: ?>
        <a id="next" class="btn dark <?= $locked ?>" href="<?= $link . ($step + 1) ?>">Next step</a>
      <?php endif ?>
    </div>
  </div>

  <div class="rail">
    <p class="label">In this lesson</p>
    <?php foreach ($steps as $i => $s): ?>
      <p>
        <span class="square <?= $i + 1 < $step ? 'done' : '' ?> <?= $i + 1 == $step ? 'now' : '' ?>"></span>
        <a href="<?= $link . ($i + 1) ?>"><?= $s['name'] ?></a>
        <?= $i + 1 == $step ? '✦' : '' ?>
      </p>
    <?php endforeach ?>
  </div>
</div>

<?php
// potato in the dirt
if ($claimed) {
    $dirt = potato([], 50) . "<p>Dug up! {$lesson['title']} is done.</p>";
} elseif (!$isLastStep) {
    $dirt = "<p>Finish all " . count($steps) . " steps to dig this one up<br>
             <em>+$reward Primospuds, and one step closer to the {$lesson['topic']} quiz</em></p>
             <span class='btn dashed'>Claim +$reward Primospuds</span>";
} elseif ($me) {
    $dirt = potato([], 50) . "<p>Dig it up!</p>
             <form method='post'><button class='btn dark'>Claim +$reward Primospuds</button></form>";
} else {
    $dirt = potato([], 50) . "<p>You finished it!</p>
             <a class='btn dark' href='login.php'>Sign in to claim this</a>";
}

if ($hasTerminal) {
    echo '<script src="terminal.js"></script>';
}
require 'footer.php';
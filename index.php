<?php
// The lesson map. Each topic is a stem growing up from the potato root thingy.
// All the x/y numbers are over in the 02 lesson map in desktop
$title = 'Your field';
$page = 'lessons';
$noDirt = true;
require 'header.php';

$topics = query('SELECT * FROM topics ORDER BY sort');
$lessons = query('SELECT * FROM lessons ORDER BY topic_id, sort');

// which lessons you've claimed and which quizzes you've passed
$claimed = [];
$passed = [];
if ($me) {
    $claims = query('SELECT lesson_id FROM lesson_claims WHERE user_id = ?', $me['user_id']);
    $claimed = array_column($claims, 'lesson_id');

    // topic_id is NULL for the final exam, so leave those out
    $quizzes = query('SELECT DISTINCT topic_id FROM attempts
                      WHERE user_id = ? AND topic_id IS NOT NULL AND score >= ?',
                      $me['user_id'], $settings['quiz_pass_score']);
    $passed = array_column($quizzes, 'topic_id');
}

// the next lesson is the first one you haventt claimed
$next = null;
foreach ($lessons as $lesson) {
    if (!$next && !in_array($lesson['lesson_id'], $claimed)) {
        $next = $lesson['lesson_id'];
    }
}

// one piece of stem itll be solid once its grown, otherwise dashed
function stem($x1, $y1, $x2, $y2, $grown) {
    $class = $grown ? 'grown' : 'dashed';
    return "<line class='$class' x1='$x1' y1='$y1' x2='$x2' y2='$y2'/>";
}

$quizReward = 5 * $settings['quiz_answer_reward'];
?>

<div class="content" style="flex: none">
  <p class="path">~/lessons</p>
  <h1>Your field</h1>
  <p class="meta">Each stem is a folder. Dig them all up, then sprout the final exam.</p>
  <p>
    <?= count($claimed) ?> of <?= count($lessons) ?> lessons ·
    <?= count($passed) ?> of <?= count($topics) ?> quizzes
  </p>
</div>

<svg class="map" viewBox="240 230 1200 770">
  <!-- grass and dirt -->
  <pattern id="speckles" width="140" height="90" patternUnits="userSpaceOnUse">
    <circle cx="20" cy="30" r="2" fill="#A88663"/>
    <circle cx="90" cy="70" r="1.6" fill="#D2B694"/>
    <circle cx="115" cy="15" r="1.4" fill="#A88663"/>
  </pattern>
  <rect x="240" y="342" width="1200" height="658" fill="#C2A07C"/>
  <rect x="240" y="342" width="1200" height="658" fill="url(#speckles)"/>
  <rect x="240" y="330" width="1200" height="12" fill="#7CC58F"/>

  <!-- final exam, with a sprout thingy -->
  <path d="<?= STAR ?>" transform="translate(810 256) scale(1.9)"/>
  <text x="840" y="262" style="font-size: 22px">Final exam</text>
  <text x="840" y="286" class="small">+<?= $settings['exam_reward'] ?> Primospuds · take it anytime</text>
  <line x1="810" y1="330" x2="810" y2="301" stroke="#2F5A3D" stroke-width="2.7"/>
  <ellipse cx="796" cy="299" rx="15" ry="7" fill="#6FBF8E" stroke="#2F5A3D" transform="rotate(25 796 299)"/>
  <ellipse cx="824" cy="299" rx="15" ry="7" fill="#6FBF8E" stroke="#2F5A3D" transform="rotate(-25 824 299)"/>
  <line class="dashed" x1="810" y1="342" x2="810" y2="372"/>

  <?php foreach ($topics as $i => $topic):
      $x = 372 + 175 * $i;
      $topicId = $topic['topic_id'];
      $quizDone = in_array($topicId, $passed);
  ?>
    <!-- draw the stem first cause potatoes are on top after -->
    <?= stem($x, 430, 810, 372, false) ?>
    <?php
      $bottom = 800;
      $grew = false;
      foreach ($lessons as $lesson) {
          if ($lesson['topic_id'] != $topicId) continue;
          $y = 840 - 80 * $lesson['sort'];
          $done = in_array($lesson['lesson_id'], $claimed);
          echo stem($x, $bottom, $x, $y, $done);
          $bottom = $y;
          if ($done) $grew = true;
      }
      echo stem($x, $bottom, $x, 430, $quizDone);
      echo stem(810, 860, $x, 800, $grew);
    ?>

    <!-- folder name and quiz -->
    <text x="<?= $x + 14 ?>" y="456" class="folder"><?= $topic['slug'] ?>/</text>
    <rect x="<?= $x - 9 ?>" y="489" width="18" height="18" class="<?= $quizDone ? 'done' : 'todo' ?>"/>
    <text x="<?= $x + 20 ?>" y="496"><?= $topic['slug'] ?> quiz</text>
    <text x="<?= $x + 20 ?>" y="513" class="small">+<?= $quizReward ?> · <?= $quizDone ? 'done' : '5 questions' ?></text>

    <!-- the lessons -->
    <?php foreach ($lessons as $lesson):
        if ($lesson['topic_id'] != $topicId) continue;
        $y = 840 - 80 * $lesson['sort'];
        $done = in_array($lesson['lesson_id'], $claimed);

        $label = '+' . $settings['lesson_reward'];
        if ($done) $label .= ' · done';
        if ($lesson['lesson_id'] == $next) $label .= ' · next up';
    ?>
      <a href="lesson.php?id=<?= $lesson['lesson_id'] ?>">
        <path d="<?= POTATO ?>" transform="translate(<?= $x ?> <?= $y ?>) scale(0.105)"
              class="<?= $done ? 'done' : 'todo' ?>" stroke-width="10"/>
        <?php if ($lesson['lesson_id'] == $next): ?>
          <path d="<?= STAR ?>" transform="translate(<?= $x - 24 ?> <?= $y ?>)"/>
        <?php endif ?>
        <text x="<?= $x + 20 ?>" y="<?= $y - 1 ?>"><?= $lesson['title'] ?></text>
        <text x="<?= $x + 20 ?>" y="<?= $y + 16 ?>" class="small"><?= $label ?></text>
      </a>
    <?php endforeach ?>
  <?php endforeach ?>

  <!-- you as the seed potato guy at the bottom -->
  <?= potato($me ? array_column($equipped, 'name') : [], 88, 'x="766" y="843"') ?>
  <text x="866" y="906" style="font-size: 15px; font-style: italic">you</text>

  <!-- legend -->
  <path d="<?= POTATO ?>" transform="translate(1110 962) scale(0.078)" class="done" stroke-width="13"/>
  <text x="1124" y="967">done</text>
  <path d="<?= POTATO ?>" transform="translate(1184 962) scale(0.078)" class="todo" stroke-width="13"/>
  <text x="1198" y="967">not yet</text>
  <path d="<?= STAR ?>" transform="translate(1270 962) scale(0.78)"/>
  <text x="1282" y="967">next</text>
  <rect x="1323" y="955" width="14" height="14" class="done"/>
  <text x="1344" y="967">quiz</text>
</svg>

<?php require 'footer.php'; ?>
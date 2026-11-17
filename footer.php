<?php
// grass and dirt on all the pages at the bottom
// set $dirt to put something in the dirt, i.e. the lesson's potato.
// drawing own dirt thing should set $noDirt.
?>
  <?php if (empty($noDirt)): ?>
    <div class="dirt"><?= $dirt ?? '' ?></div>
  <?php endif ?>
</main>
</body>
</html>
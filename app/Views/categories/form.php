<div class="card auth-card">
  <h1><?= e($title) ?></h1>

  <form method="POST" action="<?= url($action) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="field <?= isset($errors['name']) ? 'has-error' : '' ?>">
      <label for="name">Category name</label>
      <input type="text" id="name" name="name" value="<?= e($name) ?>" placeholder="e.g. Electronics">
      <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?>
    </div>

    <button type="submit" class="btn block">Save</button>
  </form>

  <p class="switch"><a href="<?= url('categories') ?>">Back to categories</a></p>
</div>
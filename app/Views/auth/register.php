<div class="card auth-card">
  <h1>Create account</h1>
  <p class="sub">It takes less than a minute.</p>

  <?php if ($errors): ?>
    <div class="alert error">Please fix the errors below.</div>
  <?php endif; ?>

  <form method="POST" action="<?= url('auth/register') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="field <?= isset($errors['name']) ? 'has-error' : '' ?>">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" placeholder="Your full name" autocomplete="name">
      <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?>
    </div>

    <div class="field <?= isset($errors['email']) ? 'has-error' : '' ?>">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" placeholder="you@example.com" autocomplete="email">
      <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
    </div>

    <div class="field <?= isset($errors['password']) ? 'has-error' : '' ?>">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="At least 8 characters" autocomplete="new-password">
      <?php if (isset($errors['password'])): ?><span class="err"><?= e($errors['password']) ?></span><?php endif; ?>
    </div>

    <div class="field <?= isset($errors['confirm']) ? 'has-error' : '' ?>">
      <label for="confirm">Confirm password</label>
      <input type="password" id="confirm" name="confirm" placeholder="Type it again" autocomplete="new-password">
      <?php if (isset($errors['confirm'])): ?><span class="err"><?= e($errors['confirm']) ?></span><?php endif; ?>
    </div>

    <button type="submit" class="btn block">Register</button>
  </form>

  <p class="switch">Already have an account? <a href="<?= url('auth/login') ?>">Login</a></p>
</div>
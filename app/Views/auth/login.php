<div class="card auth-card">
  <h1>Welcome back</h1>
  <p class="sub">Log in to manage your stock.</p>

  <?php foreach ($errors as $er): ?>
    <div class="alert error"><?= e($er) ?></div>
  <?php endforeach; ?>

  <!-- novalidate: PHP does all the checking, so the server-side rules are tested -->
  <form method="POST" action="<?= url('auth/login') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="you@example.com" autocomplete="email">
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password">
    </div>

    <button type="submit" class="btn block">Login</button>
  </form>

  <p class="switch">New here? <a href="<?= url('auth/register') ?>">Create an account</a></p>
</div>
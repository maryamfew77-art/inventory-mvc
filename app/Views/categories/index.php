<div class="top">
  <div>
    <h1>Categories (<?= count($rows) ?>)</h1>
    <?php if (!is_admin()): ?><p class="sub">Only an admin can change categories.</p><?php endif; ?>
  </div>
  <?php if (is_admin()): ?>
    <a class="btn" href="<?= url('categories/create') ?>">+ Add Category</a>
  <?php endif; ?>
</div>

<div class="table-wrap">
  <table>
    <tr>
      <th>#</th><th>Name</th><th>Products</th>
      <?php if (is_admin()): ?><th>Actions</th><?php endif; ?>
    </tr>

    <?php if (!$rows): ?>
      <tr><td colspan="4" class="empty">No categories yet.</td></tr>
    <?php endif; ?>

    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['name']) ?></td>
        <td><span class="badge"><?= (int)$r['products'] ?></span></td>
        <?php if (is_admin()): ?>
          <td class="actions">
            <a class="btn small ghost" href="<?= url('categories/edit/' . (int)$r['id']) ?>">Edit</a>
            <form method="POST" action="<?= url('categories/delete/' . (int)$r['id']) ?>" class="inline"
                  onsubmit="return confirm('Delete this category? Its products will become uncategorized.')">
              <?= csrf_field() ?>
              <button type="submit" class="btn small red">Delete</button>
            </form>
          </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
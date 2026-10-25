<div class="top">
  <div>
    <h1>Products (<?= (int)$total ?>)</h1>
    <p class="sub">Search, add and edit your stock.</p>
  </div>
  <a class="btn" href="<?= url('products/create') ?>">+ Add Product</a>
</div>

<form method="GET" action="<?= url('products') ?>" class="search">
  <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by name or SKU">
  <button type="submit" class="btn">Search</button>
  <?php if ($q !== ''): ?><a class="btn ghost" href="<?= url('products') ?>">Clear</a><?php endif; ?>
</form>

<div class="table-wrap">
  <table>
    <tr>
      <th>#</th><th>Photo</th><th>Name</th><th>SKU</th><th>Category</th><th>Supplier</th>
      <th>Price</th><th>Qty</th><th>Status</th><th>Actions</th>
    </tr>

    <?php if (!$rows): ?>
      <tr><td colspan="10" class="empty">No products found.</td></tr>
    <?php endif; ?>

    <?php foreach ($rows as $r): ?>
      <?php $low = (int)$r['quantity'] <= (int)$r['reorder_level']; ?>   <!-- at or below the reorder level -->
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td>
          <?php if ($r['photo']): ?>
            <img class="thumb" src="<?= url('uploads/' . rawurlencode($r['photo'])) ?>" alt="">
          <?php endif; ?>
        </td>
        <td><?= e($r['name']) ?></td>
        <td><?= e($r['sku']) ?></td>
        <td><?= e($r['category'] ?? '-') ?></td>
        <td><?= e($r['supplier'] ?? '-') ?></td>
        <td>Rs. <?= number_format((float)$r['price'], 2) ?></td>
        <td><?= (int)$r['quantity'] ?></td>
        <td><span class="badge <?= $low ? 'low' : 'ok' ?>"><?= $low ? 'Low stock' : 'In stock' ?></span></td>
        <td class="actions">
          <a class="btn small ghost" href="<?= url('products/edit/' . (int)$r['id']) ?>">Edit</a>

          <?php if (is_admin()): ?>   <!-- only admins see Delete (the server checks it too) -->
            <form method="POST" action="<?= url('products/delete/' . (int)$r['id']) ?>" class="inline"
                  onsubmit="return confirm('Delete this product?')">
              <?= csrf_field() ?>
              <input type="hidden" name="q" value="<?= e($q) ?>">
              <input type="hidden" name="page" value="<?= (int)$page ?>">
              <button type="submit" class="btn small red">Delete</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php if ($pages > 1): ?>
  <div class="pager">
    <?php if ($page > 1): ?>
      <a href="<?= url('products?' . http_build_query(['q' => $q, 'page' => $page - 1])) ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
      <a class="<?= $i === $page ? 'active' : '' ?>"
         href="<?= url('products?' . http_build_query(['q' => $q, 'page' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $pages): ?>
      <a href="<?= url('products?' . http_build_query(['q' => $q, 'page' => $page + 1])) ?>">Next &raquo;</a>
    <?php endif; ?>
  </div>
<?php endif; ?>
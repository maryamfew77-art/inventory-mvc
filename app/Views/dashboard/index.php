<div class="top">
  <div>
    <h1>Hello, <?= e($_SESSION['user']['name']) ?></h1>
    <p class="sub">
      Your stock at a glance &middot;
      <span class="badge <?= e($_SESSION['user']['role']) ?>"><?= e($_SESSION['user']['role']) ?></span>
      <?= is_admin() ? 'can manage everything' : 'can view, add and edit products' ?>
    </p>
  </div>
  <a class="btn" href="<?= url('products/create') ?>">+ Add Product</a>
</div>

<div class="stats">
  <div class="stat">
    <span>Total products</span>
    <strong><?= (int)$stats['total'] ?></strong>
  </div>
  <div class="stat gold">
    <span>Categories</span>
    <strong><?= (int)$categories ?></strong>
  </div>
  <div class="stat warn">
    <span>Low stock items</span>
    <strong><?= (int)$stats['low'] ?></strong>
  </div>
  <div class="stat warn">
    <span>Out of stock</span>
    <strong><?= (int)$stats['out_of_stock'] ?></strong>
  </div>
  <div class="stat good">
    <span>Total stock value</span>
    <strong>Rs. <?= number_format((float)$stats['stock_value']) ?></strong>
  </div>
  <div class="stat">
    <span>Added today</span>
    <strong><?= (int)$stats['added_today'] ?></strong>
  </div>
</div>

<div class="card">
  <h2>Needs restocking</h2>

  <?php if (!$lowStock): ?>
    <p class="empty">Nothing is low on stock. Well done!</p>

  <?php else: ?>
    <div class="table-wrap">
      <table>
        <tr><th>Product</th><th>SKU</th><th>Category</th><th>Qty</th><th>Reorder level</th><th>Action</th></tr>
        <?php foreach ($lowStock as $r): ?>
          <tr>
            <td><?= e($r['name']) ?></td>
            <td><?= e($r['sku']) ?></td>
            <td><?= e($r['category'] ?? '-') ?></td>
            <td><span class="badge low"><?= (int)$r['quantity'] ?></span></td>
            <td><?= (int)$r['reorder_level'] ?></td>
            <td><a class="btn small ghost" href="<?= url('products/edit/' . (int)$r['id']) ?>">Update stock</a></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endif; ?>
</div>
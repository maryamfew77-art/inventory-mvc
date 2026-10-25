<?php $isEdit = str_contains($action, 'update'); ?>

<div class="card">
  <h1><?= e($title) ?></h1>
  <p class="sub"><?= $isEdit ? 'Change the details and save.' : 'Fill in the details of the new product.' ?></p>

  <?php if ($errors): ?>
    <div class="alert error">Please fix the errors below.</div>
  <?php endif; ?>

  <!-- novalidate: PHP does all the checking -->
    <form method="POST" action="<?= url($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <div class="row">
      <div class="field <?= isset($errors['name']) ? 'has-error' : '' ?>">
        <label for="name">Product name *</label>
        <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" placeholder="e.g. Wireless Mouse">
        <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?>
      </div>

      <div class="field <?= isset($errors['sku']) ? 'has-error' : '' ?>">
        <label for="sku">SKU (stock code) *</label>
        <input type="text" id="sku" name="sku" value="<?= e($old['sku']) ?>" placeholder="e.g. EL-005">
        <?php if (isset($errors['sku'])): ?><span class="err"><?= e($errors['sku']) ?></span><?php endif; ?>
      </div>
    </div>

    <div class="row">
      <div class="field <?= isset($errors['category_id']) ? 'has-error' : '' ?>">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
          <option value="">-- None --</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (string)$c['id'] === (string)$old['category_id'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['category_id'])): ?><span class="err"><?= e($errors['category_id']) ?></span><?php endif; ?>
      </div>

      <div class="field <?= isset($errors['supplier_id']) ? 'has-error' : '' ?>">
        <label for="supplier_id">Supplier</label>
        <select id="supplier_id" name="supplier_id">
          <option value="">-- None --</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (string)$s['id'] === (string)$old['supplier_id'] ? 'selected' : '' ?>>
              <?= e($s['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['supplier_id'])): ?><span class="err"><?= e($errors['supplier_id']) ?></span><?php endif; ?>
      </div>
    </div>

    <div class="row">
      <div class="field <?= isset($errors['price']) ? 'has-error' : '' ?>">
        <label for="price">Price (Rs.) *</label>
        <input type="text" id="price" name="price" value="<?= e($old['price']) ?>" placeholder="e.g. 1500" inputmode="decimal">
        <?php if (isset($errors['price'])): ?><span class="err"><?= e($errors['price']) ?></span><?php endif; ?>
      </div>

      <div class="field <?= isset($errors['quantity']) ? 'has-error' : '' ?>">
        <label for="quantity">Quantity in stock *</label>
        <input type="text" id="quantity" name="quantity" value="<?= e($old['quantity']) ?>" inputmode="numeric">
        <?php if (isset($errors['quantity'])): ?><span class="err"><?= e($errors['quantity']) ?></span><?php endif; ?>
      </div>
    </div>

    <div class="field <?= isset($errors['reorder_level']) ? 'has-error' : '' ?>">
      <label for="reorder_level">Reorder level *</label>
      <input type="text" id="reorder_level" name="reorder_level" value="<?= e($old['reorder_level']) ?>" inputmode="numeric">
      <span class="hint">At or below this quantity, the product shows as "Low stock".</span>
      <?php if (isset($errors['reorder_level'])): ?><span class="err"><?= e($errors['reorder_level']) ?></span><?php endif; ?>
    </div>
    
         <div class="field <?= isset($errors['photo']) ? 'has-error' : '' ?>">
      <label for="photo">Product photo (JPG, PNG or WEBP, max 2 MB)</label>

      <?php if (!empty($photo)): ?>
        <img class="thumb" src="<?= url('uploads/' . rawurlencode($photo)) ?>" alt="Current photo"
             style="width:90px;height:90px;margin-bottom:8px">
        <span class="hint">Current photo. Choose a new file only if you want to replace it.</span>
      <?php endif; ?>

      <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp">
      <span class="hint">If the form shows errors, choose the photo again (browsers clear file fields).</span>
      <?php if (isset($errors['photo'])): ?><span class="err"><?= e($errors['photo']) ?></span><?php endif; ?>
    </div>

    <button type="submit" class="btn">Save product</button>
    <a class="btn ghost" href="<?= url('products') ?>">Cancel</a>
  </form>
</div>
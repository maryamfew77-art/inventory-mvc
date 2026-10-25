<?php
class ProductsController extends Controller
{
    private Product $model;

    public function __construct()
    {
        $this->requireLogin();   // the whole controller sits behind the login
        $this->model = new Product();
    }

    // READ: list + search + pagination.  Address: /products?q=pen&page=2
    public function index(): void
    {
        $q       = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
        $perPage = 10;
        $total   = $this->model->count($q);
        $pages   = max(1, (int)ceil($total / $perPage));
        $page    = min($pages, max(1, (int)($_GET['page'] ?? 1)));   // keep the page between 1 and the last

        $this->view('products/index', [
            'title' => 'Products',
            'rows'  => $this->model->paginate($q, $perPage, ($page - 1) * $perPage),
            'q' => $q, 'page' => $page, 'pages' => $pages, 'total' => $total,
        ]);
    }

    // CREATE: show the empty form.  Address: /products/create
    public function create(): void
    {
        $blank = [
            'name' => '', 'sku' => '', 'price' => '', 'quantity' => '0', 'reorder_level' => '5',
            'category_id' => '', 'supplier_id' => '',
        ];
        $this->form($blank, [], 'products/store');
    }

    // CREATE: save it
    public function store(): void
    {
        if (!$this->isPost()) { $this->redirect('products'); }
        $this->verifyCsrf();

        [$old, $clean, $errors] = $this->validated(0);
        if ($errors) {
            $this->form($old, $errors, 'products/store');
            return;
        }

        $this->model->create($clean);   // $clean['photo'] is the saved file name (or null)
        $this->flash('Product added.');
        $this->redirect('products');
    }

    // UPDATE: show the form with the saved values.  Address: /products/edit/5
    public function edit($id = 0): void
    {
        $p = $this->model->find((int)$id);
        if (!$p) {
            $this->flash('Product not found.', 'error');
            $this->redirect('products');
        }

        $old = [
            'name' => $p['name'], 'sku' => $p['sku'], 'price' => $p['price'],
            'quantity' => (string)$p['quantity'], 'reorder_level' => (string)$p['reorder_level'],
            'category_id' => (string)($p['category_id'] ?? ''), 'supplier_id' => (string)($p['supplier_id'] ?? ''),
        ];
        $this->form($old, [], 'products/update/' . (int)$id, $p['photo']);   // PHASE 3: show the current photo
    }

    // UPDATE: save it
    public function update($id = 0): void
    {
        if (!$this->isPost()) { $this->redirect('products'); }
        $this->verifyCsrf();

        $id      = (int)$id;
        $product = $this->model->find($id);   // PHASE 3: we need the old photo name
        if (!$product) {
            $this->flash('Product not found.', 'error');
            $this->redirect('products');
        }

        [$old, $clean, $errors] = $this->validated($id);
        if ($errors) {
            $this->form($old, $errors, 'products/update/' . $id, $product['photo']);
            return;
        }

        $this->model->update($id, $clean);

        // PHASE 3: a new photo replaced the old one, so delete the old file from the disk
        if ($clean['photo'] !== null) {
            Uploader::delete($product['photo']);
        }

        $this->flash('Product updated.');
        $this->redirect('products');
    }

    // DELETE: POST only (never a link), with CSRF, and admin only
    public function delete($id = 0): void
    {
        if (!$this->isPost()) { $this->redirect('products'); }
        $this->requireAdmin();   // a normal user is stopped here, even if they send the request by hand
        $this->verifyCsrf();

        $id      = (int)$id;
        $product = $this->model->find($id);

        if ($product) {
            $this->model->delete($id);
            Uploader::delete($product['photo']);   // PHASE 3: remove the photo file too
            $this->flash('Product deleted.');
        } else {
            $this->flash('Product not found.', 'error');
        }

        // Back to the same search and page
        $back = http_build_query(['q' => $this->input('q'), 'page' => max(1, (int)$this->input('page'))]);
        $this->redirect('products?' . $back);
    }

    // ---- private helpers ----

    // Returns [raw text for the form, clean typed data for the database, errors]
    private function validated(int $id): array
    {
        $old = [
            'name'          => $this->input('name'),
            'sku'           => strtoupper($this->input('sku')),
            'price'         => $this->input('price'),
            'quantity'      => $this->input('quantity'),
            'reorder_level' => $this->input('reorder_level'),
            'category_id'   => $this->input('category_id'),
            'supplier_id'   => $this->input('supplier_id'),
        ];
        $errors = [];

        $len = mb_strlen($old['name']);
        if ($len < 2 || $len > 150) {
            $errors['name'] = 'Name must be between 2 and 150 characters.';
        }

        if (!preg_match('/^[A-Z0-9_-]{2,50}$/', $old['sku'])) {
            $errors['sku'] = 'SKU must be 2 to 50 characters: letters, numbers, - or _ only.';
        } elseif ($this->model->skuExists($old['sku'], $id)) {
            $errors['sku'] = 'This SKU is already used by another product.';
        }

        // filter_var gives false when the text is not a valid number
        $price = filter_var($old['price'], FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0 || $price > 99999999.99) {
            $errors['price'] = 'Price must be a number from 0 to 99,999,999.99.';
        }

        $range = ['options' => ['min_range' => 0, 'max_range' => 1000000]];
        $qty = filter_var($old['quantity'], FILTER_VALIDATE_INT, $range);
        if ($qty === false) {
            $errors['quantity'] = 'Quantity must be a whole number from 0 to 1,000,000.';
        }

        $reorder = filter_var($old['reorder_level'], FILTER_VALIDATE_INT, $range);
        if ($reorder === false) {
            $errors['reorder_level'] = 'Reorder level must be a whole number from 0 to 1,000,000.';
        }

        // Category and supplier are optional, but a chosen one must really exist
        $categoryId = null;
        if ($old['category_id'] !== '') {
            $cid = filter_var($old['category_id'], FILTER_VALIDATE_INT);
            if ($cid === false || !(new Category())->find($cid)) {
                $errors['category_id'] = 'Please choose a valid category.';
            } else {
                $categoryId = $cid;
            }
        }

        $supplierId = null;
        if ($old['supplier_id'] !== '') {
            $sid = filter_var($old['supplier_id'], FILTER_VALIDATE_INT);
            if ($sid === false || !(new Supplier())->find($sid)) {
                $errors['supplier_id'] = 'Please choose a valid supplier.';
            } else {
                $supplierId = $sid;
            }
        }

        // PHASE 3: the photo is handled LAST, and only if everything else is valid.
        // That way a form with errors never leaves a stray file in the uploads folder
        $photo = null;
        if (!$errors) {
            [$photo, $uploadError] = Uploader::image($_FILES['photo'] ?? []);
            if ($uploadError) {
                $errors['photo'] = $uploadError;
            }
        }

        $clean = [
            'name' => $old['name'], 'sku' => $old['sku'],
            'price' => round((float)$price, 2), 'quantity' => (int)$qty, 'reorder_level' => (int)$reorder,
            'category_id' => $categoryId, 'supplier_id' => $supplierId,
            'photo' => $photo,   // null = no new photo (the old one stays)
        ];
        return [$old, $clean, $errors];
    }

    private function form(array $old, array $errors, string $action, ?string $photo = null): void
    {
        $this->view('products/form', [
            'title'      => str_contains($action, 'update') ? 'Edit Product' : 'Add Product',
            'old'        => $old,
            'errors'     => $errors,
            'action'     => $action,
            'photo'      => $photo,   // PHASE 3: the current photo, if any
            'categories' => (new Category())->all(),
            'suppliers'  => (new Supplier())->all(),
        ]);
    }
}
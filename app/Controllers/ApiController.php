<?php
// JSON API. Every request must send the header:  X-API-KEY: <your key>
//   GET  /api/products        list (search and pages):  /api/products?q=pen&page=2
//   GET  /api/products/5      one product
//   POST /api/products        create a product (JSON body)
class ApiController extends Controller
{
    // Sends JSON and stops the script
    private function json(array $payload, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // API key check: the key travels in a header, never in the address
    private function authorize(): void
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if (!is_string($key) || !hash_equals(API_KEY, $key)) {   // hash_equals: safe comparison
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    }

    // One database row -> clean API output (numbers as numbers, the photo as a full link)
    private function present(array $r): array
    {
        return [
            'id'            => (int)$r['id'],
            'name'          => $r['name'],
            'sku'           => $r['sku'],
            'price'         => (float)$r['price'],
            'quantity'      => (int)$r['quantity'],
            'reorder_level' => (int)$r['reorder_level'],
            'low_stock'     => (int)$r['quantity'] <= (int)$r['reorder_level'],
            'category'      => $r['category'] ?? null,
            'supplier'      => $r['supplier'] ?? null,
            'photo_url'     => $r['photo'] ? url('uploads/' . rawurlencode($r['photo'])) : null,
            'created_at'    => $r['created_at'],
        ];
    }

    // Address: /api/products  or  /api/products/5
    public function products($id = null): void
    {
        $this->authorize();   // no key, no data

        try {
            $model  = new Product();
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method === 'GET') {
                if ($id === null) {
                    $this->listProducts($model);
                } else {
                    $this->oneProduct($model, (string)$id);
                }
            }

            if ($method === 'POST' && $id === null) {
                $this->createProduct($model);
            }

            header('Allow: GET, POST');
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        } catch (Throwable $e) {
            error_log($e->getMessage());   // the real error goes to the log ...
            $this->json(['success' => false, 'message' => 'Server error'], 500);   // ... the caller sees a safe text
        }
    }

    private function listProducts(Product $model): void
    {
        $q       = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
        $perPage = 20;
        $total   = $model->count($q);
        $pages   = max(1, (int)ceil($total / $perPage));
        $page    = min($pages, max(1, (int)($_GET['page'] ?? 1)));
        $rows    = $model->paginate($q, $perPage, ($page - 1) * $perPage);

        $this->json([
            'success' => true,
            'page'    => $page,
            'pages'   => $pages,
            'total'   => $total,
            'count'   => count($rows),
            'data'    => array_map(fn($r) => $this->present($r), $rows),
        ]);
    }

    private function oneProduct(Product $model, string $id): void
    {
        $row = ctype_digit($id) ? $model->findDetailed((int)$id) : null;   // only digits are valid ids

        if (!$row) {
            $this->json(['success' => false, 'message' => 'Product not found'], 404);
        }
        $this->json(['success' => true, 'data' => $this->present($row)]);
    }

    private function createProduct(Product $model): void
    {
        $in = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($in)) {
            $this->json(['success' => false, 'message' => 'Please send valid JSON'], 400);
        }

        // JSON can hold true/false/lists. Only text and numbers are accepted as values
        $plain = fn($v) => (is_int($v) || is_float($v) || is_string($v)) ? $v : null;
        $errors = [];

        $name = is_string($in['name'] ?? null) ? trim($in['name']) : '';
        $sku  = is_string($in['sku'] ?? null) ? strtoupper(trim($in['sku'])) : '';

        $len = mb_strlen($name);
        if ($len < 2 || $len > 150) {
            $errors['name'] = 'Name must be between 2 and 150 characters.';
        }

        if (!preg_match('/^[A-Z0-9_-]{2,50}$/', $sku)) {
            $errors['sku'] = 'SKU must be 2 to 50 characters: letters, numbers, - or _ only.';
        } elseif ($model->skuExists($sku)) {
            $errors['sku'] = 'This SKU is already used by another product.';
        }

        $price = filter_var($plain($in['price'] ?? null), FILTER_VALIDATE_FLOAT);
        if ($price === false || $price < 0 || $price > 99999999.99) {
            $errors['price'] = 'Price must be a number from 0 to 99,999,999.99.';
        }

        $range   = ['options' => ['min_range' => 0, 'max_range' => 1000000]];
        $qty     = filter_var($plain($in['quantity'] ?? 0), FILTER_VALIDATE_INT, $range);        // default 0
        $reorder = filter_var($plain($in['reorder_level'] ?? 5), FILTER_VALIDATE_INT, $range);   // default 5
        if ($qty === false) {
            $errors['quantity'] = 'Quantity must be a whole number from 0 to 1,000,000.';
        }
        if ($reorder === false) {
            $errors['reorder_level'] = 'Reorder level must be a whole number from 0 to 1,000,000.';
        }

        // Category and supplier are optional, but a given one must really exist
        $categoryId = null;
        if (($in['category_id'] ?? null) !== null) {
            $cid = filter_var($plain($in['category_id']), FILTER_VALIDATE_INT);
            if ($cid === false || !(new Category())->find($cid)) {
                $errors['category_id'] = 'This category does not exist.';
            } else {
                $categoryId = $cid;
            }
        }

        $supplierId = null;
        if (($in['supplier_id'] ?? null) !== null) {
            $sid = filter_var($plain($in['supplier_id']), FILTER_VALIDATE_INT);
            if ($sid === false || !(new Supplier())->find($sid)) {
                $errors['supplier_id'] = 'This supplier does not exist.';
            } else {
                $supplierId = $sid;
            }
        }

        if ($errors) {
            $this->json(['success' => false, 'errors' => $errors], 422);
        }

        $newId = $model->create([
            'name' => $name, 'sku' => $sku, 'price' => round((float)$price, 2),
            'quantity' => (int)$qty, 'reorder_level' => (int)$reorder,
            'category_id' => $categoryId, 'supplier_id' => $supplierId, 'photo' => null,
        ]);

        header('Location: ' . url('api/products/' . $newId));   // where the new product lives
        $this->json(['success' => true, 'id' => $newId, 'message' => 'Product created'], 201);
    }
}
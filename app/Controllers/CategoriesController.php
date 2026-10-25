<?php
class CategoriesController extends Controller
{
    private Category $model;

    public function __construct()
    {
        $this->requireLogin();   // everyone must be logged in
        $this->model = new Category();
    }

    // Address: /categories  (users can view, only admins see the buttons)
    public function index(): void
    {
        $this->view('categories/index', ['title' => 'Categories', 'rows' => $this->model->all()]);
    }

    // Address: /categories/create
    public function create(): void
    {
        $this->requireAdmin();   // server-side role check
        $this->form('', [], 'categories/store');
    }

    public function store(): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) { $this->redirect('categories'); }
        $this->verifyCsrf();

        [$name, $errors] = $this->validated(0);
        if ($errors) {
            $this->form($name, $errors, 'categories/store');
            return;
        }

        $this->model->create($name);
        $this->flash('Category added.');
        $this->redirect('categories');
    }

    // Address: /categories/edit/3
    public function edit($id = 0): void
    {
        $this->requireAdmin();
        $cat = $this->model->find((int)$id);
        if (!$cat) {
            $this->flash('Category not found.', 'error');
            $this->redirect('categories');
        }
        $this->form($cat['name'], [], 'categories/update/' . (int)$id);
    }

    public function update($id = 0): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) { $this->redirect('categories'); }
        $this->verifyCsrf();

        $id = (int)$id;
        if (!$this->model->find($id)) { $this->redirect('categories'); }

        [$name, $errors] = $this->validated($id);
        if ($errors) {
            $this->form($name, $errors, 'categories/update/' . $id);
            return;
        }

        $this->model->update($id, $name);
        $this->flash('Category updated.');
        $this->redirect('categories');
    }

    // DELETE: POST only (never a link), with CSRF, admin only
    public function delete($id = 0): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) { $this->redirect('categories'); }
        $this->verifyCsrf();

        $id = (int)$id;
        if ($this->model->find($id)) {
            $this->model->delete($id);
            $this->flash('Category deleted.');
        }
        $this->redirect('categories');
    }

    // ---- private helpers ----
    private function validated(int $id): array
    {
        $name   = $this->input('name');
        $errors = [];

        $len = mb_strlen($name);
        if ($len < 2 || $len > 100) {
            $errors['name'] = 'Category name must be between 2 and 100 characters.';
        } elseif ($this->model->nameExists($name, $id)) {
            $errors['name'] = 'This category already exists.';
        }
        return [$name, $errors];
    }

    private function form(string $name, array $errors, string $action): void
    {
        $this->view('categories/form', [
            'title' => str_contains($action, 'update') ? 'Edit Category' : 'Add Category',
            'name' => $name, 'errors' => $errors, 'action' => $action,
        ]);
    }
}
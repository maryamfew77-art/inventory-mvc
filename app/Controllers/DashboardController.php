<?php
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->requireLogin();   // the whole controller sits behind the login
    }

    // Address: /dashboard
    public function index(): void
    {
        $products = new Product();

        $this->view('dashboard/index', [
            'title'      => 'Dashboard',
            'stats'      => $products->stats(),
            'categories' => count((new Category())->all()),
            'lowStock'   => $products->lowStock(5),
        ]);
    }
}
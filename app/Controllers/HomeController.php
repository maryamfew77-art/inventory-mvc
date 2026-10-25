<?php
class HomeController extends Controller
{
    // Address: /   (sends you to the right place)
    public function index(): void
    {
        $this->redirect(is_logged_in() ? 'dashboard' : 'auth/login');
    }
}
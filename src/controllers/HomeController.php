<?php
namespace App\Controllers;

use App\Models\AppointmentModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    private $model;
    private $view;

    public function __construct(AppointmentModel $model, $view)
    {
        $this->model = $model;
        $this->view = $view;
    }

    public function index()
    {
        session_start();
        $user = $_SESSION['user'] ?? null; // Проверяем, авторизован ли пользователь
        $services = $this->model->getServices(); // Получаем список услуг
    
        return $this->view->render('home.twig', [
            'services' => $services,
            'user' => $user // Передаём данные о пользователе в шаблон
        ]);
    }
}
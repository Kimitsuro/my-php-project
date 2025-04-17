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
        return $this->view->render('home.twig');
    }
}
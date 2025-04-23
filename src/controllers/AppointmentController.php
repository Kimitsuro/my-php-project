<?php
namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\AppointmentServiceModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppointmentController
{
    private $model;
    private $serviceModel;
    private $view;
    private $request;

    public function __construct(AppointmentModel $model, AppointmentServiceModel $serviceModel, $view, Request $request)
    {
        $this->model = $model;
        $this->serviceModel = $serviceModel;
        $this->view = $view;
        $this->request = $request;
    }

    public function list()
    {
        $appointments = $this->model->getAllAppointments();
        return $this->view->render('appointments.twig', ['appointments' => $appointments]);
    }

    public function listWithServices()
    {
        $appointments = $this->serviceModel->getAllWithServices();
        return $this->view->render('appointments_with_services.twig', ['appointments' => $appointments]);
    }

    public function create()
    {
        if ($this->request->getMethod() !== 'POST') {
            return new Response('Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
    
        $data = $this->request->request->all();
        $errors = $this->validate($data);
    
        if (!empty($errors)) {
            $services = $this->model->getServices(); // Получаем список услуг
            return $this->view->render('home.twig', ['errors' => $errors, 'data' => $data, 'services' => $services]);
        }
    
        if ($this->model->createAppointment($data)) {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Запись сохранена! <a href="/">Назад</a>';
            exit;
        }
    
        return new Response('Ошибка при сохранении записи', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function validate(array $data): array
    {
        $errors = [];
    
        // Фильтрация данных
        $data['name'] = trim($data['name'] ?? '');
        $data['phone'] = preg_replace('/\D/', '', $data['phone'] ?? '');
        $data['datetime'] = trim($data['datetime'] ?? '');
    
        // Валидация данных
        if (empty($data['name'])) {
            $errors[] = "Имя обязательно для заполнения.";
        } elseif (!preg_match('/^[\p{L}\-]+$/u', $data['name'])) {
            $errors[] = "Имя может содержать только буквы и тире.";
        }
    
        if (empty($data['phone'])) {
            $errors[] = "Телефон обязателен для заполнения.";
        } elseif (!preg_match('/^\d{9}(\d{2})?$/', $data['phone'])) {
            $errors[] = "Номер телефона должен содержать 9 или 11 цифр.";
        }
    
        if (empty($data['datetime'])) {
            $errors[] = "Дата и время обязательны для заполнения.";
        } else {
            $selectedDateTime = new \DateTime($data['datetime']);
            $currentDateTime = new \DateTime();
            if ($selectedDateTime < $currentDateTime) {
                $errors[] = "Нельзя записаться на прошедшую дату.";
            }
        }
    
        return $errors;
    }
}
<?php
namespace App\Controllers;

use App\Models\AppointmentModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateController
{
    private $model;
    private $view;
    private $request;

    public function __construct(AppointmentModel $model, $view, Request $request)
    {
        $this->model = $model;
        $this->view = $view;
        $this->request = $request;
    }

    public function search()
    {
        $phone = $this->request->query->get('phone', '');
        $appointments = $this->model->findByPhone($phone);
        
        return $this->view->render('update.twig', [
            'appointments' => $appointments,
            'phone' => $phone
        ]);
    }

    public function editForm()
    {
        $id = (int)$this->request->request->get('id');
        $appointment = $this->model->findById($id);
        
        if (!$appointment) {
            return new Response('Запись не найдена', Response::HTTP_NOT_FOUND);
        }
        
        return $this->view->render('update.twig', ['appointment' => $appointment]);
    }

    public function update()
    {
        $data = $this->request->request->all();
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $appointment = $this->model->findById((int)$data['id']);
            return $this->view->render('update.twig', [
                'appointment' => $appointment,
                'errors' => $errors
            ]);
        }

        if ($this->model->updateAppointment($data)) {
            return new Response('Данные обновлены! <a href="/update">Назад</a>');
        }

        return new Response('Ошибка при обновлении записи', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function validate(array $data): array
    {
        $errors = [];
    
        // Фильтрация данных
        $data['name'] = trim($data['name'] ?? '');
        $data['phone'] = preg_replace('/\D/', '', $data['phone'] ?? '');
    
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
    
        return $errors;
    }
}
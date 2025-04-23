<?php
namespace App\Controllers;

use App\Models\UserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController
{
    private $userModel;
    private $view;
    private $request;

    public function __construct(UserModel $userModel, $view, Request $request)
    {
        $this->userModel = $userModel;
        $this->view = $view;
        $this->request = $request;
    }

    public function register()
    {
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->request->all();
            $errors = $this->validateRegistration($data);

            if (!empty($errors)) {
                return $this->view->render('register.twig', ['errors' => $errors]);
            }

            if ($this->userModel->register($data['username'], $data['email'], $data['password'])) {
                return new Response('Регистрация успешна! <a href="/login">Войти</a>');
            }

            return new Response('Ошибка регистрации', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->view->render('register.twig');
    }

    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['username'])) {
            $errors[] = 'Имя пользователя обязательно.';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный email.';
        } elseif ($this->userModel->findByEmail($data['email'])) {
            $errors[] = 'Email уже используется.';
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов.';
        }

        return $errors;
    }

    public function login()
    {
        if ($this->request->getMethod() === 'POST') {
            $data = $this->request->request->all();
            $user = $this->userModel->login($data['email'], $data['password']);

            if ($user) {
                session_start();
                $_SESSION['user'] = $user;
                return new Response('Вход выполнен! <a href="/profile">Личный кабинет</a>');
            }

            return $this->view->render('login.twig', ['error' => 'Неверный email или пароль.']);
        }

        return $this->view->render('login.twig');
    }

    public function profile()
    {
        session_start();

        if (!isset($_SESSION['user'])) {
            return new Response('Вы не авторизованы. <a href="/login">Войти</a>', Response::HTTP_FORBIDDEN);
        }

        return $this->view->render('profile.twig', ['user' => $_SESSION['user']]);
    }

    public function logout()
    {
        session_start();
        session_destroy();
        return new Response('Вы вышли из системы. <a href="/">На главную</a>');
    }
}

<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\View;
use App\Core\Database;
use App\Models\AppointmentModel;
use App\Models\AppointmentServiceModel;
use Symfony\Component\HttpFoundation\Request;

// Инициализация компонентов
$request = Request::createFromGlobals(); // Получаем текущий запрос
$view = new View(); // Инициализация шаблонизатора (Twig)
$db = new Database(require __DIR__ . '/../config/database.php'); // Инициализация базы данных
$appointmentModel = new AppointmentModel($db); // Инициализация модели записи на прием
$appointmentServiceModel = new AppointmentServiceModel($db);

// Инициализация контроллеров
$homeController = new App\Controllers\HomeController($appointmentModel, $view);
$updateController = new App\Controllers\UpdateController($appointmentModel, $view, $request);
$appointmentController = new App\Controllers\AppointmentController($appointmentModel, $appointmentServiceModel, $view, $request);
$authController = new App\Controllers\AuthController(new App\Models\UserModel($db), $view, $request);

// Настройка роутера
$router = new Router();

$router->addRoute('GET', '/', function() use ($homeController) {
    return new \Symfony\Component\HttpFoundation\Response($homeController->index());
}); 

$router->addRoute('POST', '/appointments', function() use ($appointmentController) {
    return new \Symfony\Component\HttpFoundation\Response($appointmentController->create());
});

$router->addRoute('GET', '/update', function() use ($updateController) {
    return new \Symfony\Component\HttpFoundation\Response($updateController->search());
});

$router->addRoute('POST', '/update/edit', function() use ($updateController) {
    return new \Symfony\Component\HttpFoundation\Response($updateController->editForm());
});

$router->addRoute('POST', '/update', function() use ($updateController) {
    return new \Symfony\Component\HttpFoundation\Response($updateController->update());
});

$router->addRoute('GET', '/appointments', function() use ($appointmentController) {
    return new \Symfony\Component\HttpFoundation\Response($appointmentController->list());
});

$router->addRoute('GET', '/appointments-with-services', function() use ($appointmentController) {
    return new \Symfony\Component\HttpFoundation\Response($appointmentController->listWithServices());
});

$router->addRoute('GET', '/register', function() use ($authController) {
    return new \Symfony\Component\HttpFoundation\Response($authController->register());
});

$router->addRoute('POST', '/register', function() use ($authController) {
    return new \Symfony\Component\HttpFoundation\Response($authController->register());
});

$router->addRoute('GET', '/login', function() use ($authController) {
    return new \Symfony\Component\HttpFoundation\Response($authController->login());
});

$router->addRoute('POST', '/login', function() use ($authController) {
    return new \Symfony\Component\HttpFoundation\Response($authController->login());
});

$router->addRoute('GET', '/profile', function() use ($authController) {
    return new \Symfony\Component\HttpFoundation\Response($authController->profile());
});

$router->addRoute('GET', '/logout', function() use ($authController) {
    return new \Symfony\Component\HttpFoundation\Response($authController->logout());
});

// Обработка запроса
$response = $router->handle($request); // Получаем ответ от роутера
$response->send(); // Отправляем ответ клиенту
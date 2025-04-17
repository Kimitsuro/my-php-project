<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Автозагрузка классов с помощью Composer

use App\Core\Router;
use App\Core\View;
use App\Models\Database;
use App\Models\AppointmentModel;
use Symfony\Component\HttpFoundation\Request;

// Инициализация компонентов
$request = Request::createFromGlobals(); // Получаем текущий запрос
$view = new View(); // Инициализация шаблонизатора (Twig)
$db = new Database(require __DIR__ . '/../config/database.php'); // Инициализация базы данных
$appointmentModel = new AppointmentModel($db); // Инициализация модели записи на прием

// Инициализация контроллеров
$homeController = new App\Controllers\HomeController($appointmentModel, $view);
$appointmentController = new App\Controllers\AppointmentController($appointmentModel, $view, $request);
$updateController = new App\Controllers\UpdateController($appointmentModel, $view, $request);

// Настройка роутера
$router = new Router();

$router->addRoute('GET', '/', function() use ($homeController) {
    return new \Symfony\Component\HttpFoundation\Response($homeController->index());
}); 

$router->addRoute('GET', '/appointments', function() use ($appointmentController) {
    return new \Symfony\Component\HttpFoundation\Response($appointmentController->list());
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

// Обработка запроса
$response = $router->handle($request); // Получаем ответ от роутера
$response->send(); // Отправляем ответ клиенту
// Закрываем соединение с базой данных
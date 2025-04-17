<?php
namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    private $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../views'); // Путь к папке с шаблонами
        $this->twig = new Environment($loader); // Инициализация Twig с загрузчиком шаблонов
    }

    public function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data); // Рендеринг шаблона с переданными данными
    }
}
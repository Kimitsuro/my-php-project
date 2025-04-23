<?php
namespace App\Models;

use App\Core\Database;

class AppointmentModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllAppointments()
    {
        $stmt = $this->db->getConnection()->query("SELECT * FROM appointments ORDER BY id ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createAppointment(array $data): bool
    {
        // Получаем название услуги по service_id
        $stmt = $this->db->getPdo()->prepare("SELECT name FROM services WHERE id = :service_id");
        $stmt->execute([':service_id' => $data['service_id']]);
        $service = $stmt->fetchColumn();
    
        $stmt = $this->db->getPdo()->prepare("
            INSERT INTO appointments (name, phone, service, service_id, master, datetime) 
            VALUES (:name, :phone, :service, :service_id, :master, :datetime)
        ");
    
        return $stmt->execute([
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':service' => $service, // Название услуги
            ':service_id' => $data['service_id'], // ID услуги
            ':master' => $data['master'],
            ':datetime' => $data['datetime']
        ]);
    }

    public function getServices(): array
    {
        $stmt = $this->db->getPdo()->query("SELECT id, name FROM services ORDER BY id ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateAppointment(array $data): bool
    {
        // Получаем название услуги по service_id
        $stmt = $this->db->getPdo()->prepare("SELECT name FROM services WHERE id = :service_id");
        $stmt->execute([':service_id' => $data['service_id']]);
        $service = $stmt->fetchColumn();
    
        $stmt = $this->db->getPdo()->prepare("
            UPDATE appointments 
            SET name = :name, 
                phone = :phone, 
                service = :service,
                service_id = :service_id,
                master = :master, 
                datetime = :datetime 
            WHERE id = :id
        ");
    
        return $stmt->execute([
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':service' => $service, // Название услуги
            ':service_id' => $data['service_id'], // ID услуги
            ':master' => $data['master'],
            ':datetime' => $data['datetime'],
            ':id' => $data['id']
        ]);
    }

    public function findByPhone(string $phone): array
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM appointments WHERE phone = :phone");
        $stmt->execute(['phone' => $phone]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->getPdo()->prepare("SELECT * FROM appointments WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    private function saveToCsv(array $data): void
    {
        $filePath = "data.csv";
        $fileExists = file_exists($filePath);
    
        // Открываем файл для записи
        $file = fopen($filePath, "a");
    
        // Если файл только что создан, добавляем заголовки
        if (!$fileExists) {
            fputcsv($file, ['Имя', 'Телефон', 'Услуга', 'Мастер', 'Дата и время'], ";");
        }
    
        // Записываем данные
        fputcsv($file, [
            $data['name'],
            $data['phone'],
            $data['service'],
            $data['master'],
            $data['datetime']
        ], ";");
    
        // Закрываем файл
        fclose($file);
    }

    public function importFromCsv(): bool
    {
        if (($handle = fopen("data.csv", "r")) !== FALSE) {
            $stmt = $this->db->getPdo()->prepare(
                "INSERT INTO appointments (name, phone, service, master, datetime) 
                VALUES (?, ?, ?, ?, ?)"
            );
            
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                // Фильтрация данных
                $data = array_map('trim', $data); // Удаляем пробелы
                $data[0] = preg_replace('/[^a-zA-Zа-яА-ЯёЁ\s\-]/u', '', $data[0]); // Имя: только буквы, пробелы и тире
                $data[1] = preg_replace('/\D/', '', $data[1]); // Телефон: только цифры
                $data[4] = trim($data[4]); // Дата и время: удаляем пробелы
                
                // Проверка на пустые значения
                if (empty($data[0]) || empty($data[1]) || empty($data[4])) {
                    continue; // Пропускаем некорректные строки
                }
    
                // Выполняем запрос
                $stmt->execute($data);
            }
            
            fclose($handle);
            return true;
        }
        
        return false;
    }
}
<?php
namespace App\Models;

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
        $stmt = $this->db->getPdo()->prepare(
            "INSERT INTO appointments (name, phone, service, master, datetime) 
            VALUES (:name, :phone, :service, :master, :datetime)"
        );

        $this->saveToCsv($data);
        
        return $stmt->execute($data);
    }

    public function updateAppointment(array $data): bool
    {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE appointments 
            SET name = :name, 
                phone = :phone, 
                service = :service, 
                master = :master, 
                datetime = :datetime 
            WHERE id = :id"
        );
        
        return $stmt->execute([
            ':name' => $data['name'],
            ':phone' => $data['phone'],
            ':service' => $data['service'],
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
        $file = fopen("data.csv", "a");
        fputcsv($file, [
            $data['name'],
            $data['phone'],
            $data['service'],
            $data['master'],
            $data['datetime']
        ], ";");
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
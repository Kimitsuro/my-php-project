<?php
namespace App\Models;

use App\Core\Database;

class AppointmentServiceModel
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAllWithServices(): array
    {
        $stmt = $this->db->getPdo()->query("
            SELECT 
                a.id AS appointment_id,
                a.name AS client_name,
                a.phone,
                a.master,
                a.datetime,
                s.name AS service_name,
                s.price,
                s.duration,
                s.description
            FROM appointments a
            LEFT JOIN services s ON a.service_id = s.id
            ORDER BY a.id ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
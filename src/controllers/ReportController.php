<?php
namespace App\Controllers;

use App\Models\AppointmentModel;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ReportController
{
    private $model;

    public function __construct(AppointmentModel $model)
    {
        $this->model = $model;
    }

    public function generatePdf()
    {
        $appointments = $this->model->getAllAppointments();

        $html = '<h1>Список записей</h1>';
        $html .= '<table border="1" style="width: 100%; border-collapse: collapse;">';
        $html .= '<tr><th>ID</th><th>Имя</th><th>Телефон</th><th>Услуга</th><th>Мастер</th><th>Дата и время</th></tr>';

        foreach ($appointments as $appointment) {
            $html .= '<tr>';
            $html .= '<td>' . $appointment['id'] . '</td>';
            $html .= '<td>' . $appointment['name'] . '</td>';
            $html .= '<td>' . $appointment['phone'] . '</td>';
            $html .= '<td>' . $appointment['service'] . '</td>';
            $html .= '<td>' . $appointment['master'] . '</td>';
            $html .= '<td>' . $appointment['datetime'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => __DIR__ . '/../../tmp',
        ]);
        $mpdf->WriteHTML($html);
        $mpdf->Output('report.pdf', 'D'); // Скачивание файла
    }

    public function generateExcel()
    {
        $appointments = $this->model->getAllAppointments();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Имя');
        $sheet->setCellValue('C1', 'Телефон');
        $sheet->setCellValue('D1', 'Услуга');
        $sheet->setCellValue('E1', 'Мастер');
        $sheet->setCellValue('F1', 'Дата и время');

        // Данные
        $row = 2;
        foreach ($appointments as $appointment) {
            $sheet->setCellValue('A' . $row, $appointment['id']);
            $sheet->setCellValue('B' . $row, $appointment['name']);
            $sheet->setCellValue('C' . $row, $appointment['phone']);
            $sheet->setCellValue('D' . $row, $appointment['service']);
            $sheet->setCellValue('E' . $row, $appointment['master']);
            $sheet->setCellValue('F' . $row, $appointment['datetime']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'report.xlsx';

        // Заголовки для скачивания
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
    }

    public function generateCsv(): Response
    {
        $appointments = $this->model->getAllAppointments();
    
        $fileName = 'report.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
    
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Имя', 'Телефон', 'Услуга', 'Мастер', 'Дата и время']);
    
        foreach ($appointments as $appointment) {
            fputcsv($output, [
                $appointment['id'],
                $appointment['name'],
                $appointment['phone'],
                $appointment['service'],
                $appointment['master'],
                $appointment['datetime']
            ]);
        }
    
        fclose($output);
    
        return new Response('', Response::HTTP_OK, ['Content-Type' => 'text/csv']);
    }
}
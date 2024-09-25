<?php

namespace FmcExample\UserPackage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\File;
use Illuminate\Bus\Batchable;


class ExportUserDataJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable, Batchable;

    protected $users;
    protected $email;
    protected $filePath;

    public function __construct($users, $email)
    {
        $this->users = $users;
        $this->email = $email;
    }

    public function handle()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Đặt tiêu đề cột
        $sheet->setCellValue('A1', 'Tên');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Ngày đăng ký');
        $sheet->setCellValue('D1', 'KYC');
        $sheet->setCellValue('E1', 'Quốc gia');
        $sheet->setCellValue('F1', 'Ngày sinh');

        $row = 2;
        foreach ($this->users as $user) {
            $sheet->setCellValue('A' . $row, $user['userName'] ?? '');
            $sheet->setCellValue('B' . $row, $user['email'] ?? '');
            $sheet->setCellValue('C' . $row, !empty($user['created_at']) ? $user['created_at']->format('Y-m-d H:i:s') : '');
            $sheet->setCellValue('D' . $row, !empty($user['is_verified']) ? 'Đã KYC' : 'Chưa KYC');
            $sheet->setCellValue('E' . $row, $user['country'] ?? '');
            $sheet->setCellValue('F' . $row, $user['birth_day'] ?? '');
            $row++;
        }

        $directoryPath = storage_path('app/public/' . $this->email);
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
        $this->filePath = $directoryPath . '/' . time() . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        try {
            $writer->save($this->filePath);
        } catch (\Exception $e) {
            Log::info('Lỗi khi lưu file: ' . $e->getMessage());
            return;
        }
    }
}

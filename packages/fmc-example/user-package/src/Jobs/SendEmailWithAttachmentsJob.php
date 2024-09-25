<?php

namespace FmcExample\UserPackage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SendEmailWithAttachmentsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    protected $email;
    protected $directoryPath;
    public $tries = 5;
    public $timeout = 3600;

    public function __construct($email)
    {
        $this->email = $email;
        $this->directoryPath = storage_path('app/public/' . $this->email);
    }

    public function handle()
    {
        try {
            Mail::send('userpackage::emails.user_data', [], function ($message) {
                $message->to($this->email)
                    ->subject('Export dữ liệu người dùng');

                $files = [];
                if (File::exists($this->directoryPath)) {
                    $files = glob($this->directoryPath . '/*');
                }
                if (!empty($files)) {
                    foreach ($files as $file) {
                        if (File::exists($file)) {
                            $message->attach($file);
                        } else {
                            Log::info('File không tồn tại: ' . $file);
                        }
                    }
                }
            });

            File::deleteDirectory($this->directoryPath);
            Cache::forget('processing_email_' . $this->email);

        } catch (\Exception $e) {
            Log::info('Lỗi khi gửi email: ' . $e->getMessage());
        }

    }

}

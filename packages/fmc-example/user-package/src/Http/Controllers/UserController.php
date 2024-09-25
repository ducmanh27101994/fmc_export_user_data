<?php

namespace FmcExample\UserPackage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use FmcExample\UserPackage\Jobs\ExportUserDataJob;
use FmcExample\UserPackage\Jobs\SendEmailWithAttachmentsJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;


class UserController extends Controller
{
    public function exportDataUsers(Request $request)
    {
        $email = $request->input('email');
        if (empty($email)) {
            return response()->json([
                'message' => 'Nhập Email người nhận thông tin',
                'status' => 400,
            ]);
        }

        $filters = $request->only(['kyc', 'country', 'min_age', 'max_age', 'start_date', 'end_date']);

        if (count(array_filter($filters)) == 0) {
            return response()->json([
                'message' => 'Bạn phải cung cấp ít nhất một điều kiện để xuất dữ liệu',
                'status' => 400,
            ]);
        }

        $validateEmail = $this->getFilteredUsers($filters, $email);

        if (!$validateEmail) {
            return response()->json([
                'message' => 'Dữ liệu đang xử lý. Hãy gửi lại yêu cầu khi email gửi hoàn thành',
                'status' => 400,
            ]);
        } else {
            return response()->json([
                'message' => 'Success',
                'status' => 200,
            ]);
        }
    }

    private function getFilteredUsers(array $filters, $email)
    {
        $query = User::query();
        if ($filters['kyc'] !== null) {
            $query->where('is_verified', $filters['kyc']);
        }
        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }
        if (!empty($filters['min_age']) || !empty($filters['max_age'])) {
            $this->filterByAgeRange($query, $filters['min_age'], $filters['max_age']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $this->filterByRegistrationDate($query, $filters['start_date'], $filters['end_date']);
        }

        if (Cache::has('processing_email_' . $email)) {
            return false;
        }
        $batchJobs = [];
        $numberChuck = 50000; // Số lượng bản ghi trên mỗi file excel
        $query->chunk($numberChuck, function ($users) use (&$batchJobs, $email) {
            $batchJobs[] = new ExportUserDataJob($users, $email);
        });

        Bus::batch($batchJobs)->then(function (Batch $batch) use ($email) {
            SendEmailWithAttachmentsJob::dispatch($email);
        })->catch(function (Batch $batch, Throwable $e) {
            Log::error("Có lỗi xảy ra trong quá trình xuất dữ liệu: " . $e->getMessage());
        })->finally(function (Batch $batch, $email) {
            Log::info("Quá trình xử lý batch đã kết thúc.");
        })->dispatch();

        Cache::put('processing_email_' . $email, true, now()->addHours(24));
        return true;
    }

    private function filterByAgeRange($query, $minAge, $maxAge)
    {
        $currentDate = Carbon::now();
        if (!empty($minAge)) {
            $minBirthDate = $currentDate->copy()->subYears($minAge)->endOfYear();
            $query->where('birth_day', '<=', $minBirthDate);
        }
        if (!empty($maxAge)) {
            $maxBirthDate = $currentDate->copy()->subYears($maxAge)->startOfYear();
            $query->where('birth_day', '>=', $maxBirthDate);
        }
    }

    private function filterByRegistrationDate($query, $startDate, $endDate)
    {
        $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay(),
        ]);
    }

}

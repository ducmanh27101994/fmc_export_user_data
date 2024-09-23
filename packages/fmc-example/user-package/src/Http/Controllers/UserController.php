<?php

namespace FmcExample\UserPackage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    public function exportDataUsers(Request $request)
    {
        $filters = $request->only(['kyc', 'country', 'min_age', 'max_age', 'start_date', 'end_date']);
        $userList = $this->getFilteredUsers($filters);

        return response()->json([
            'message' => 'Success',
            'status' => 200,
            'data' => $userList
        ]);
    }

    private function getFilteredUsers(array $filters)
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

        return $query->get()->map([$this, 'formatUserData']);
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

    public function formatUserData($user)
    {
        return [
            'userName' => $user->name ?? '',
            'email' => $user->email ?? '',
            'Ngày đăng ký' => $user->created_at->format('Y-m-d H:i:s') ?? '',
            'KYC' => $user->is_verified ? 'Đã KYC' : 'Chưa KYC',
            'Quốc gia' => $user->country ?? '',
            'Ngày sinh' => $user->birth_day ?? '',
        ];
    }


}

Tạo fake dữ liệu 500k:
1. php artisan migrate --path=/packages/fmc-example/user-package/src/database/migrations/2024_01_01_000000_add_test_fields_to_users_table.php
2. php artisan db:seed --class="FmcExample\UserPackage\Database\Seeders\UserSeeder"
3. composer require phpoffice/phpspreadsheet

Đầu vào filter:
- end_date, start_date (Y-m-d) -- Ngày đăng ký
- kyc (1 đã kyc, 0 chưa kyc) -- Đã KYC, chưa KYC
- country (string) -- Đã KYC, chưa KYC
- min_age, max_age (10, 20, int)  -- Độ tuổi


Package Login Google:
composer require laravel/socialite
php artisan migrate --path=/packages/fmc-example/google-login/src/database/migrations/2024_09_23__add_fields_google_to_users_table.php

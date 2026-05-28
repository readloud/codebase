Aplikasi akan berjalan di `http://localhost:3000` dengan proxy API ke `http://localhost:8000` (backend Laravel). 

Pastikan backend Laravel Anda sudah berjalan sebelum mencoba mengakses API.

Berikut adalah default login credentials untuk aplikasi CodeBase:

## **Default Admin Account**

```json
{
  "username": "admin",
  "email": "admin@codebase.com",
  "phone": "081234567890",
  "password": "admin123"
}
```

## **Default User Account**

```json
{
  "username": "user",
  "email": "user@codebase.com",
  "phone": "081234567891",
  "password": "user123"
}
```

## **Register New Account**

Jika ingin mendaftar akun baru, gunakan endpoint:

```bash
POST /api/auth/register
Content-Type: application/json

{
    "username": "newuser",
    "email": "newuser@example.com",
    "phone": "081234567893",
    "password": "password123",
    "password_confirmation": "password123"
}
```

## **Login dengan Berbagai Method**

Login bisa menggunakan:
- **Username**: `admin` atau `user`
- **Email**: `admin@codebase.com` atau `user@codebase.com`
- **Phone**: `081234567890` atau `081234567891`

## **Testing OTP Verification**

Untuk testing OTP, sistem akan mengirim kode ke nomor telepon. Untuk development, Anda bisa melihat OTP di:

1. **Log Laravel**: `storage/logs/laravel.log`
2. **Database**: `users` table column `phone_otp`

Atau gunakan OTP default untuk testing: `123456`

```php
// app/Http/Controllers/AuthController.php - Add test mode
public function sendOTP($user)
{
    if (app()->environment('local')) {
        $otp = 123456; // Default OTP for testing
    } else {
        $otp = rand(100000, 999999);
    }
    
    $user->phone_otp = $otp;
    $user->phone_otp_expires_at = now()->addMinutes(5);
    $user->save();
    
    // Log for development
    \Log::info("OTP for {$user->phone}: {$otp}");
    
    return $otp;
}
```

## **Quick Login for Development**

Anda juga bisa menambahkan login cepat untuk development:

```php
// app/Http/Controllers/AuthController.php - Add quick login for development
public function quickLogin(Request $request)
{
    if (app()->environment('local')) {
        $role = $request->get('role', 'user');
        
        $user = User::where('role_id', $role === 'admin' ? 1 : 2)->first();
        
        if ($user) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'user' => $user,
                'token' => $token
            ]);
        }
    }
    
    return response()->json(['message' => 'Quick login only available in development'], 400);
}
```

## **Login Component dengan Demo Credentials**

```jsx
// Add to Login.jsx - Demo credentials hint
<div className="mt-4 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
  <p className="text-sm text-gray-600 dark:text-gray-400 mb-1">Demo Accounts:</p>
  <p className="text-xs text-gray-500">Admin: admin@codebase.com / admin123</p>
  <p className="text-xs text-gray-500">User: user@codebase.com / user123</p>
</div>
```
```bash
# Backend Deployment
composer install --no-dev
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate --force
php artisan db:seed
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Frontend Build
npm install

# development
npm run dev

# production
npm run build

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
sudo systemctl restart supervisor
```
## **Add to crontab -e**
```
# Sync products every hour
0 * * * * php /path/to/backend/artisan schedule:run >> /dev/null 2>&1

# Check transaction status every 5 minutes
*/5 * * * * php /path/to/backend/artisan queue:work --stop-when-empty

# Generate sitemap daily
0 0 * * * php /path/to/backend/artisan sitemap:generate
```
Untuk production, pastikan untuk mengubah password default dan menghapus demo accounts jika tidak diperlukan.

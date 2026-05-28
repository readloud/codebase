protected $middlewareGroups = [
    'api' => [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\SecurityHeaders::class,
    'auth' => \App\Http\Middleware\Authenticate::class,
    'role' => \App\Http\Middleware\RoleMiddleware::class,
    ],
];

// Custom rate limiter in RouteServiceProvider
protected function configureRateLimiting()
{
    RateLimiter::for('api', function ($job) {
        return Limit::perMinute(60)->by($job->user()?->id ?: $job->ip());
    });
    
    RateLimiter::for('auth', function ($job) {
        return Limit::per minute(5)->by($job->ip());
    });
}

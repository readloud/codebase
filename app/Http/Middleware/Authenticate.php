// app/Http/Middleware/Authenticate.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        // Decode temporary token
        $decoded = base64_decode($token);
        $parts = explode('|', $decoded);
        
        if (count($parts) !== 3) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        
        $userId = $parts[0];
        $user = User::find($userId);
        
        if (!$user || $user->remember_token !== $token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        return $next($request);
    }
}
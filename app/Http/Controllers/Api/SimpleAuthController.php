<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SimpleAuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check if user exists and password is correct
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Generate a unique token
            $token = hash('sha256', Str::random(40));

            // Create API token record
            ApiToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'name' => 'mobile_app',
                'expires_at' => now()->addDays(30), // Token expires in 30 days
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->name, // Split name for Flutter compatibility
                        'last_name' => $user->lastname ?? '', // Use lastname field if available
                        'email' => $user->email,
                        'employee_id' => $user->employee_id ?? null,
                        'department' => $user->department ?? null,
                        'position' => $user->position ?? null,
                        'phone_number' => $user->phone ?? null,
                        'phone' => $user->phone ?? null,
                        'address' => $user->address ?? null,
                    ],
                    'token' => $token
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->name,
                        'last_name' => $user->lastname ?? '',
                        'email' => $user->email,
                        'employee_id' => $user->employee_id ?? null,
                        'department' => $user->department ?? null,
                        'position' => $user->position ?? null,
                        'phone_number' => $user->phone ?? null,
                        'phone' => $user->phone ?? null,
                        'address' => $user->address ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        try {
            $token = $this->getTokenFromRequest($request);
            
            if ($token) {
                // Delete the token
                ApiToken::where('token', $token)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string|max:500',
            ]);

            $user->update($request->only(['name', 'phone', 'address']));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->name,
                        'last_name' => $user->lastname ?? '',
                        'email' => $user->email,
                        'employee_id' => $user->employee_id ?? null,
                        'department' => $user->department ?? null,
                        'position' => $user->position ?? null,
                        'phone_number' => $user->phone ?? null,
                        'phone' => $user->phone ?? null,
                        'address' => $user->address ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract token from request
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    /**
     * Get user from token
     */
    private function getUserFromToken(Request $request): ?User
    {
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            return null;
        }

        $apiToken = ApiToken::where('token', $token)
            ->with('user')
            ->first();

        if (!$apiToken || $apiToken->isExpired()) {
            return null;
        }

        // Mark token as used
        $apiToken->markAsUsed();

        return $apiToken->user;
    }
}

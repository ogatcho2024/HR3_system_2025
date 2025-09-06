<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }


    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required|string|max:20',
            'position' => 'required|string|max:100',
            'account_type' => 'required|in:1,2',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            // Handle optional photo
            $photoPath = null;
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            // Create the user
            $user = User::create([
                'name' => $validatedData['name'],
                'lastname' => $validatedData['lastname'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'photo' => $photoPath,
                'phone' => $validatedData['phone'],
                'position' => $validatedData['position'],
                'account_type' => $validatedData['account_type'],
            ]);

            // Create welcome notification
            $this->notificationService->createWelcomeNotification($user);

            // Redirect on success
            return redirect()->route('auth.success')->with('success', 'Account created successfully.');

        } catch (\Exception $e) {
            // Log and show error
            logger()->error('Registration failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Registration failed. Please try again.']);
        }
    }

}

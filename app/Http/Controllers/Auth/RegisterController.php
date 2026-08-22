<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Admin\NewDoctorRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // =========================
        // 1. VALIDATION
        // =========================
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,doctor' // কে register করছে
        ]);

        // =========================
        // 2. USER CREATE
        // =========================
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,

            // password hash
            'password' => Hash::make($request->password),

            // SaaS field (optional for now)
            'tenant_id' => null,
        ]);

        // =========================
        // 3. ROLE ASSIGN (MAIN PART)
        // =========================
        $user->assignRole($request->role);

        if ($request->role === 'doctor') {
            $user->update(['is_approved' => false]);
            User::role('admin')->each(fn ($admin) => $admin->notify(new NewDoctorRegistration($user)));
        }

        // =========================
        // 4. RESPONSE
        // =========================
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->load('roles')
        ]);
    }
}
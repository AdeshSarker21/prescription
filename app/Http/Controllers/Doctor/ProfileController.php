<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $doctor = auth()->user();

        return view('doctor.profile.index', compact('doctor'));
    }

    public function edit(): View
    {
        $doctor = auth()->user();

        return view('doctor.profile.edit', compact('doctor'));
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->id,
            'phone' => 'nullable|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'experience_years' => 'nullable|numeric|min:0',
            'clinic_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $doctor->update($data);

        return redirect()->route('doctor.profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $doctor = auth()->user();

        if (!Hash::check($request->current_password, $doctor->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $doctor->update([
            'password' => $request->password,
        ]);

        return redirect()->route('doctor.profile')
            ->with('success', 'Password changed successfully.');
    }
}

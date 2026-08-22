<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $doctor = auth()->user();

        return view('doctor.settings.index', compact('doctor'));
    }

    public function updateClinic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'clinic_name' => 'required|string|max:255',
            'address' => 'nullable|string',
        ]);

        auth()->user()->update($data);

        return redirect()->route('doctor.settings')
            ->with('success', 'Clinic information updated successfully.');
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'visiting_hours' => 'nullable|string',
        ]);

        auth()->user()->update($data);

        return redirect()->route('doctor.settings')
            ->with('success', 'Visiting hours updated successfully.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        return redirect()->route('doctor.settings')
            ->with('success', 'Notification settings updated successfully.');
    }

    public function updatePrescription(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'designation_title' => 'nullable|string|max:255',
            'affiliated_hospital' => 'nullable|string|max:255',
            'sub_specialties' => 'nullable|string',
            'chambers' => 'nullable|json',
            'emergency_contact' => 'nullable|string|max:255',
            'prescription_heading' => 'nullable|string|max:255',
            'prescription_slogan' => 'nullable|string|max:255',
        ]);

        if ($request->filled('sub_specialties')) {
            $data['sub_specialties'] = array_map('trim', explode(',', $request->sub_specialties));
        } else {
            $data['sub_specialties'] = [];
        }

        auth()->user()->update($data);

        return redirect()->route('doctor.settings')
            ->with('success', 'Prescription layout settings updated successfully.');
    }
}

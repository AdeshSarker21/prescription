<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        $doctors = User::role('doctor')->with('tenant')->get();

        $doctors->each(function ($doctor) {
            $doctor->patient_count = $doctor->patients()->count();
        });

        return view('admin.doctors.index', compact('doctors'));
    }

    public function show(User $user): View
    {
        $user->load('subscriptions.plan');
        $user->patient_count = $user->patients()->count();
        return view('admin.doctors.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.doctors.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'specialization_bn' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'qualification_bn' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:100'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'clinic_name_bn' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'address_bn' => ['nullable', 'string', 'max:500'],
            'visiting_hours' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'designation_title' => ['nullable', 'string', 'max:255'],
            'designation_title_bn' => ['nullable', 'string', 'max:255'],
            'affiliated_hospital' => ['nullable', 'string', 'max:255'],
            'affiliated_hospital_bn' => ['nullable', 'string', 'max:255'],
            'sub_specialties' => ['nullable', 'string'],
            'sub_specialties_bn' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_contact_bn' => ['nullable', 'string', 'max:255'],
            'prescription_heading' => ['nullable', 'string', 'max:255'],
            'prescription_heading_bn' => ['nullable', 'string', 'max:255'],
            'prescription_slogan' => ['nullable', 'string', 'max:255'],
            'prescription_slogan_bn' => ['nullable', 'string', 'max:255'],
            'chambers' => ['nullable', 'json'],
        ]);

        $data = $request->only([
            'name', 'name_bn', 'email', 'phone',
            'specialization', 'specialization_bn',
            'qualification', 'qualification_bn',
            'license_number', 'experience_years',
            'clinic_name', 'clinic_name_bn',
            'address', 'address_bn',
            'visiting_hours', 'status',
            'designation_title', 'designation_title_bn',
            'affiliated_hospital', 'affiliated_hospital_bn',
            'emergency_contact', 'emergency_contact_bn',
            'prescription_heading', 'prescription_heading_bn',
            'prescription_slogan', 'prescription_slogan_bn',
        ]);

        foreach (['sub_specialties', 'sub_specialties_bn'] as $field) {
            if ($request->filled($field)) {
                $data[$field] = array_map('trim', explode(',', $request->$field));
            } else {
                $data[$field] = [];
            }
        }

        if ($request->filled('chambers')) {
            $data['chambers'] = json_decode($request->chambers, true);
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor updated successfully.');
    }

    public function create(): View
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_bn' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'specialization_bn' => ['nullable', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'qualification_bn' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:50'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:100'],
            'clinic_name' => ['nullable', 'string', 'max:255'],
            'clinic_name_bn' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'address_bn' => ['nullable', 'string', 'max:500'],
            'visiting_hours' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'designation_title' => ['nullable', 'string', 'max:255'],
            'designation_title_bn' => ['nullable', 'string', 'max:255'],
            'affiliated_hospital' => ['nullable', 'string', 'max:255'],
            'affiliated_hospital_bn' => ['nullable', 'string', 'max:255'],
            'sub_specialties' => ['nullable', 'string'],
            'sub_specialties_bn' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_contact_bn' => ['nullable', 'string', 'max:255'],
            'prescription_heading' => ['nullable', 'string', 'max:255'],
            'prescription_heading_bn' => ['nullable', 'string', 'max:255'],
            'prescription_slogan' => ['nullable', 'string', 'max:255'],
            'prescription_slogan_bn' => ['nullable', 'string', 'max:255'],
            'chambers' => ['nullable', 'json'],
        ]);

        $data = $request->only([
            'name', 'name_bn', 'email', 'phone',
            'specialization', 'specialization_bn',
            'qualification', 'qualification_bn',
            'license_number', 'experience_years',
            'clinic_name', 'clinic_name_bn',
            'address', 'address_bn', 'visiting_hours',
            'designation_title', 'designation_title_bn',
            'affiliated_hospital', 'affiliated_hospital_bn',
            'emergency_contact', 'emergency_contact_bn',
            'prescription_heading', 'prescription_heading_bn',
            'prescription_slogan', 'prescription_slogan_bn',
        ]);

        foreach (['sub_specialties', 'sub_specialties_bn'] as $field) {
            if ($request->filled($field)) {
                $data[$field] = array_map('trim', explode(',', $request->$field));
            } else {
                $data[$field] = [];
            }
        }

        if ($request->filled('chambers')) {
            $data['chambers'] = json_decode($request->chambers, true);
        }
        $data['password'] = bcrypt($request->password);
        $data['is_approved'] = true;
        $data['status'] = 'active';

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);
        $user->assignRole('doctor');

        $basic = \App\Models\Plan::where('slug', 'basic')->first();
        if ($basic) {
            $user->subscriptions()->create([
                'plan_id' => $basic->id,
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);
        }

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor created successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Doctor deleted successfully.');
    }
}

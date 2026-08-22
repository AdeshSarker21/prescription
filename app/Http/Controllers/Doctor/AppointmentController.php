<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');

        $appointments = Appointment::where('doctor_id', auth()->id())
            ->with('patient')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);

        return view('doctor.appointments.index', compact('appointments'));
    }

    public function create(): View
    {
        $patients = Patient::where('doctor_id', auth()->id())->get();

        return view('doctor.appointments.create', compact('patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['doctor_id'] = auth()->id();
        $data['status'] = 'scheduled';

        Appointment::create($data);

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment): View
    {
        if ($appointment->doctor_id !== auth()->id()) {
            abort(403);
        }

        $appointment->load('patient');

        return view('doctor.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment): View
    {
        if ($appointment->doctor_id !== auth()->id()) {
            abort(403);
        }

        $patients = Patient::where('doctor_id', auth()->id())->get();

        return view('doctor.appointments.edit', compact('appointment', 'patients'));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        if ($appointment->doctor_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($data);

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        if ($appointment->doctor_id !== auth()->id()) {
            abort(403);
        }

        $appointment->delete();

        return redirect()->route('doctor.appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }

    public function today(): View
    {
        $appointments = Appointment::where('doctor_id', auth()->id())
            ->whereDate('appointment_date', today())
            ->with('patient')
            ->orderBy('appointment_date')
            ->get();

        return view('doctor.appointments.today', compact('appointments'));
    }

    public function complete(Appointment $appointment): RedirectResponse
    {
        if ($appointment->doctor_id !== auth()->id()) {
            abort(403);
        }

        $appointment->update(['status' => 'completed']);

        return redirect()->back()->with('success', 'Appointment marked as completed.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        if ($appointment->doctor_id !== auth()->id()) {
            abort(403);
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Appointment cancelled successfully.');
    }
}

<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\AppointmentBooked;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();

        $query = Appointment::whereIn('doctor_id', $doctorIds)
            ->with('patient', 'doctor')
            ->orderBy('appointment_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%");
            });
        }

        $appointments = $query->paginate(20);
        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();

        return view('assistant.appointments.index', compact('appointments', 'doctors'));
    }

    public function create(Request $request)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();
        $selectedDoctor = $request->get('doctor_id');
        $patients = Patient::whereIn('doctor_id', $doctorIds)
            ->orderBy('name')
            ->get();

        $bookedSlots = [];
        if ($selectedDoctor) {
            $date = $request->get('date', now()->toDateString());
            $bookedSlots = Appointment::where('doctor_id', $selectedDoctor)
                ->whereDate('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->pluck('appointment_date')
                ->map(function ($dt) {
                    return \Carbon\Carbon::parse($dt)->format('H:i');
                })
                ->toArray();
        }

        return view('assistant.appointments.create', compact('doctors', 'selectedDoctor', 'patients', 'bookedSlots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($request->doctor_id, $doctorIds)) {
            return back()->with('error', 'You are not authorized to book for this doctor.');
        }

        Appointment::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'appointment_date' => $request->appointment_date,
            'reason' => $request->reason,
            'status' => 'scheduled',
            'booked_by' => auth()->id(),
        ]);

        $appointment = Appointment::where('doctor_id', $request->doctor_id)
            ->where('patient_id', $request->patient_id)
            ->latest()
            ->first();

        if ($appointment) {
            User::find($request->doctor_id)->notify(new AppointmentBooked($appointment));
        }

        return redirect()->route('assistant.appointments.index')
            ->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $appointment->load('patient', 'doctor', 'bookedBy');

        return view('assistant.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();
        $patients = Patient::whereIn('doctor_id', $doctorIds)->orderBy('name')->get();

        return view('assistant.appointments.edit', compact('appointment', 'doctors', 'patients'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $appointment->update([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'appointment_date' => $request->appointment_date,
            'reason' => $request->reason,
        ]);

        return redirect()->route('assistant.appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $request->validate([
            'new_date' => 'required|date|after:now',
        ]);

        $appointment->update([
            'appointment_date' => $request->new_date,
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    public function cancel(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $appointment->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $appointment->update(['status' => 'completed']);

        return back()->with('success', 'Appointment marked as completed.');
    }

    public function availability(Request $request, int $doctorId)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($doctorId, $doctorIds)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $date = $request->get('date', now()->toDateString());

        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('appointment_date')
            ->map(function ($dt) {
                return \Carbon\Carbon::parse($dt)->format('H:i');
            })
            ->toArray();

        return response()->json(['booked_slots' => $bookedSlots]);
    }
}

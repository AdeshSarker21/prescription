<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\SerialSession;
use App\Models\SmartSerialChamber;
use App\Models\SmartSerialSetting;
use App\Models\User;
use App\Notifications\AppointmentBooked;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Find or create an active SerialSession for the given doctor and date,
     * then generate and return the next serial number.
     *
     * @return array{session: SerialSession, formatted_serial: string, serial_number: int}|null
     */
    private function generateSerialForAppointment(int $doctorId, string $appointmentDate, ?int $chamberId = null): ?array
    {
        $settings = SmartSerialSetting::firstOrCreate(
            ['doctor_id' => $doctorId],
            [
                'starting_serial_number' => 1,
                'max_serial' => 999,
                'max_queue_size' => 50,
                'prefix' => '',
                'serial_reset_daily' => true,
            ]
        );

        $sessionDate = Carbon::parse($appointmentDate)->toDateString();

        // Find or create a session for this doctor on this date
        $session = SerialSession::where('doctor_id', $doctorId)
            ->where('session_date', $sessionDate)
            ->where('status', '!=', 'closed')
            ->first();

        if (!$session) {
            // Determine chamber: use provided chamber_id or first active chamber for the doctor
            if (!$chamberId) {
                $chamber = SmartSerialChamber::where('doctor_id', $doctorId)
                    ->where('is_active', true)
                    ->first();
                $chamberId = $chamber?->id;
            }

            $startingSerial = $settings->getEffectiveStartingSerial(
                $chamberId ? SmartSerialChamber::find($chamberId) : null
            );

            $session = SerialSession::create([
                'doctor_id' => $doctorId,
                'chamber_id' => $chamberId,
                'session_date' => $sessionDate,
                'status' => 'active',
                'daily_serial_counter' => $startingSerial - 1,
                'current_serial' => 0,
                'total_patients' => 0,
                'started_at' => now(),
            ]);
        }

        // Check queue limits
        $currentTotal = $session->patientQueues()->count();
        if ($currentTotal >= $settings->max_queue_size) {
            return null;
        }
        if ($session->daily_serial_counter >= $settings->max_serial) {
            return null;
        }

        // Generate serial
        $formattedSerial = $session->generateNextSerial($settings);

        // Duplicate prevention loop
        $attempts = 0;
        while ($session->serialExists($formattedSerial) && $attempts < 10) {
            $formattedSerial = $session->generateNextSerial($settings);
            $attempts++;
        }

        return [
            'session' => $session,
            'formatted_serial' => $formattedSerial,
            'serial_number' => $session->daily_serial_counter,
        ];
    }
    public function index(Request $request)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();

        $query = Appointment::whereIn('doctor_id', $doctorIds)
            ->with('patient', 'doctor', 'patientQueue')
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

        // Check for duplicate: same patient + doctor + date already scheduled
        $duplicateExists = Appointment::where('doctor_id', $request->doctor_id)
            ->where('patient_id', $request->patient_id)
            ->whereDate('appointment_date', $request->appointment_date)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($duplicateExists) {
            return back()->with('error', 'This patient already has a scheduled appointment with this doctor on the selected date.');
        }

        $appointment = Appointment::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'appointment_date' => $request->appointment_date,
            'reason' => $request->reason,
            'status' => 'scheduled',
            'booked_by' => auth()->id(),
        ]);

        // Auto-generate serial for the appointment
        $serialData = $this->generateSerialForAppointment(
            (int) $request->doctor_id,
            $request->appointment_date
        );

        $serialMessage = '';
        if ($serialData) {
            PatientQueue::create([
                'serial_session_id' => $serialData['session']->id,
                'doctor_id' => $request->doctor_id,
                'patient_id' => $request->patient_id,
                'appointment_id' => $appointment->id,
                'serial_number' => $serialData['serial_number'],
                'formatted_serial' => $serialData['formatted_serial'],
                'status' => PatientQueue::STATUS_WAITING,
                'priority' => 'normal',
                'notes' => $request->reason,
            ]);

            $serialData['session']->increment('total_patients');

            $serialMessage = " | Serial: {$serialData['formatted_serial']}";
        }

        // Notify the doctor
        User::find($request->doctor_id)->notify(new AppointmentBooked($appointment));

        return redirect()->route('assistant.appointments.show', $appointment)
            ->with('success', "Appointment booked successfully.{$serialMessage}");
    }

    public function show(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $appointment->load('patient', 'doctor', 'bookedBy', 'patientQueue.session');

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

        $oldDoctorId = $appointment->doctor_id;
        $oldDate = $appointment->appointment_date->toDateString();
        $newDoctorId = (int) $request->doctor_id;
        $newDate = Carbon::parse($request->appointment_date)->toDateString();

        $appointment->update([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'appointment_date' => $request->appointment_date,
            'reason' => $request->reason,
        ]);

        // Sync the linked serial if doctor or date changed
        $existingQueue = $appointment->patientQueue;
        if ($existingQueue) {
            if ($oldDoctorId !== $newDoctorId || $oldDate !== $newDate) {
                // Cancel the old serial
                $existingQueue->transitionTo(PatientQueue::STATUS_CANCELLED, 'assistant', 'Appointment rescheduled - old serial cancelled');

                // Generate new serial for the updated appointment
                $serialData = $this->generateSerialForAppointment(
                    $newDoctorId,
                    $request->appointment_date
                );

                if ($serialData) {
                    PatientQueue::create([
                        'serial_session_id' => $serialData['session']->id,
                        'doctor_id' => $newDoctorId,
                        'patient_id' => $request->patient_id,
                        'appointment_id' => $appointment->id,
                        'serial_number' => $serialData['serial_number'],
                        'formatted_serial' => $serialData['formatted_serial'],
                        'status' => PatientQueue::STATUS_WAITING,
                        'priority' => $existingQueue->priority,
                        'notes' => $request->reason,
                    ]);

                    $serialData['session']->increment('total_patients');
                }
            }
        } else {
            // No existing serial - generate one
            $serialData = $this->generateSerialForAppointment(
                $newDoctorId,
                $request->appointment_date
            );

            if ($serialData) {
                PatientQueue::create([
                    'serial_session_id' => $serialData['session']->id,
                    'doctor_id' => $newDoctorId,
                    'patient_id' => $request->patient_id,
                    'appointment_id' => $appointment->id,
                    'serial_number' => $serialData['serial_number'],
                    'formatted_serial' => $serialData['formatted_serial'],
                    'status' => PatientQueue::STATUS_WAITING,
                    'priority' => 'normal',
                    'notes' => $request->reason,
                ]);

                $serialData['session']->increment('total_patients');
            }
        }

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

        $newDate = Carbon::parse($request->new_date)->toDateString();

        $appointment->update([
            'appointment_date' => $request->new_date,
            'status' => 'scheduled',
        ]);

        // Sync the linked serial
        $existingQueue = $appointment->patientQueue;
        if ($existingQueue) {
            // Cancel the old serial (it was for the old date)
            $existingQueue->transitionTo(PatientQueue::STATUS_CANCELLED, 'assistant', 'Appointment rescheduled - old serial cancelled');

            // Generate new serial for the new date
            $serialData = $this->generateSerialForAppointment(
                (int) $appointment->doctor_id,
                $request->new_date
            );

            if ($serialData) {
                PatientQueue::create([
                    'serial_session_id' => $serialData['session']->id,
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'appointment_id' => $appointment->id,
                    'serial_number' => $serialData['serial_number'],
                    'formatted_serial' => $serialData['formatted_serial'],
                    'status' => PatientQueue::STATUS_WAITING,
                    'priority' => $existingQueue->priority,
                    'notes' => $appointment->reason,
                ]);

                $serialData['session']->increment('total_patients');
            }
        } else {
            // No existing serial - generate one
            $serialData = $this->generateSerialForAppointment(
                (int) $appointment->doctor_id,
                $request->new_date
            );

            if ($serialData) {
                PatientQueue::create([
                    'serial_session_id' => $serialData['session']->id,
                    'doctor_id' => $appointment->doctor_id,
                    'patient_id' => $appointment->patient_id,
                    'appointment_id' => $appointment->id,
                    'serial_number' => $serialData['serial_number'],
                    'formatted_serial' => $serialData['formatted_serial'],
                    'status' => PatientQueue::STATUS_WAITING,
                    'priority' => 'normal',
                    'notes' => $appointment->reason,
                ]);

                $serialData['session']->increment('total_patients');
            }
        }

        return back()->with('success', 'Appointment rescheduled successfully.');
    }

    public function cancel(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $appointment->update(['status' => 'cancelled']);

        // Mark the linked serial as cancelled
        $existingQueue = $appointment->patientQueue;
        if ($existingQueue && $existingQueue->status !== PatientQueue::STATUS_CANCELLED) {
            $existingQueue->transitionTo(PatientQueue::STATUS_CANCELLED, 'assistant', 'Appointment cancelled');
        }

        return back()->with('success', 'Appointment cancelled.');
    }

    public function complete(Appointment $appointment)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        if (!in_array($appointment->doctor_id, $doctorIds)) {
            abort(403);
        }

        $appointment->update(['status' => 'completed']);

        // If the linked serial is still active (waiting/preparing/calling), mark as completed
        $existingQueue = $appointment->patientQueue;
        if ($existingQueue && in_array($existingQueue->status, [
            PatientQueue::STATUS_WAITING,
            PatientQueue::STATUS_PREPARING,
            PatientQueue::STATUS_CALLING,
            PatientQueue::STATUS_INSIDE,
        ])) {
            $existingQueue->transitionTo(PatientQueue::STATUS_COMPLETED, 'assistant', 'Appointment completed');
        }

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

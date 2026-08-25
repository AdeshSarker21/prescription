<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientQueue;
use App\Models\Prescription;
use App\Models\SerialSession;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPatients = Patient::where('doctor_id', auth()->id())->count();
        $todayAppointments = Appointment::where('doctor_id', auth()->id())
            ->whereDate('appointment_date', today())
            ->count();
        $totalPrescriptions = Prescription::where('doctor_id', auth()->id())->count();

        $prescriptionStatusCounts = [
            'pending_investigations' => Prescription::where('doctor_id', auth()->id())
                ->where('status', Prescription::STATUS_INVESTIGATION_PENDING)->count(),
            'report_received' => Prescription::where('doctor_id', auth()->id())
                ->where('status', Prescription::STATUS_REPORT_RECEIVED)->count(),
            'active_treatments' => Prescription::where('doctor_id', auth()->id())
                ->where('status', Prescription::STATUS_TREATMENT_STARTED)->count(),
            'follow_ups' => Prescription::where('doctor_id', auth()->id())
                ->where('status', Prescription::STATUS_FOLLOW_UP)->count(),
            'completed' => Prescription::where('doctor_id', auth()->id())
                ->where('status', Prescription::STATUS_COMPLETED)->count(),
        ];

        $todayFollowUps = Prescription::where('doctor_id', auth()->id())
            ->where('status', Prescription::STATUS_FOLLOW_UP)
            ->whereDate('follow_up_date', today())
            ->with('patient')
            ->get();

        $recentAppointments = Appointment::where('doctor_id', auth()->id())
            ->with('patient')
            ->latest()
            ->take(5)
            ->get();

        $activePlan = auth()->user()->activePlan()?->name ?? 'N/A';

        $hasSmartSerial = auth()->user()->hasModulePermission('smart_serial', 'smart-serial-manage');
        $activeSession = null;
        $queueStats = ['waiting' => 0, 'called' => 0, 'in_consultation' => 0, 'completed' => 0, 'skipped' => 0];
        $nextPatient = null;

        if ($hasSmartSerial) {
            $activeSession = SerialSession::where('doctor_id', auth()->id())
                ->where('session_date', today())
                ->where('status', 'active')
                ->with('chamber')
                ->first();

            if ($activeSession) {
                $queueStats['waiting'] = $activeSession->patientQueues()->where('status', 'waiting')->count();
                $queueStats['called'] = $activeSession->patientQueues()->where('status', 'called')->count();
                $queueStats['in_consultation'] = $activeSession->patientQueues()->where('status', 'in_consultation')->count();
                $queueStats['completed'] = $activeSession->patientQueues()->where('status', 'completed')->count();
                $queueStats['skipped'] = $activeSession->patientQueues()->where('status', 'skipped')->count();

                $nextPatient = $activeSession->patientQueues()
                    ->where('status', 'waiting')
                    ->with('patient')
                    ->orderByRaw("CASE priority WHEN 'emergency' THEN 1 WHEN 'urgent' THEN 2 WHEN 'vip' THEN 3 ELSE 4 END")
                    ->orderBy('serial_number')
                    ->first();
            }
        }

        return view('doctor.dashboard.index', compact(
            'totalPatients',
            'todayAppointments',
            'totalPrescriptions',
            'prescriptionStatusCounts',
            'todayFollowUps',
            'recentAppointments',
            'activePlan',
            'hasSmartSerial',
            'activeSession',
            'queueStats',
            'nextPatient'
        ));
    }
}

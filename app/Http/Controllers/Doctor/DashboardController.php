<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Prescription;
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

        return view('doctor.dashboard.index', compact(
            'totalPatients',
            'todayAppointments',
            'totalPrescriptions',
            'prescriptionStatusCounts',
            'todayFollowUps',
            'recentAppointments',
            'activePlan'
        ));
    }
}

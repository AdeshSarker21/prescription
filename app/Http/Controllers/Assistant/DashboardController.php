<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $today = Carbon::today();

        // Stats
        $todayAppointments = Appointment::whereIn('doctor_id', $doctorIds)
            ->whereDate('appointment_date', $today)
            ->count();

        $upcomingAppointments = Appointment::whereIn('doctor_id', $doctorIds)
            ->where('appointment_date', '>=', $today)
            ->where('status', 'scheduled')
            ->count();

        $completedToday = Appointment::whereIn('doctor_id', $doctorIds)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed')
            ->count();

        $totalPatients = Patient::whereIn('doctor_id', $doctorIds)->count();

        // Today's queue
        $todayQueue = Appointment::whereIn('doctor_id', $doctorIds)
            ->whereDate('appointment_date', $today)
            ->with('patient', 'doctor')
            ->orderBy('appointment_date')
            ->get();

        // Upcoming appointments (next 7 days)
        $upcoming = Appointment::whereIn('doctor_id', $doctorIds)
            ->where('appointment_date', '>', $today)
            ->where('appointment_date', '<=', $today->copy()->addDays(7))
            ->where('status', 'scheduled')
            ->with('patient', 'doctor')
            ->orderBy('appointment_date')
            ->limit(10)
            ->get();

        // Recent patients (for waiting list)
        $recentPatients = Patient::whereIn('doctor_id', $doctorIds)
            ->latest('created_at')
            ->take(10)
            ->get();

        // Calendar data (appointments this month)
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $calendarData = Appointment::whereIn('doctor_id', $doctorIds)
            ->whereBetween('appointment_date', [$monthStart, $monthEnd])
            ->selectRaw('DATE(appointment_date) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->get()
            ->groupBy('date')
            ->map(function ($dayAppointments) {
                return [
                    'total' => $dayAppointments->sum('count'),
                    'scheduled' => $dayAppointments->where('status', 'scheduled')->sum('count'),
                    'completed' => $dayAppointments->where('status', 'completed')->sum('count'),
                    'cancelled' => $dayAppointments->where('status', 'cancelled')->sum('count'),
                ];
            });

        // Assigned doctors
        $doctors = auth()->user()->assignedDoctors()->get();

        return view('assistant.dashboard.index', compact(
            'todayAppointments',
            'upcomingAppointments',
            'completedToday',
            'totalPatients',
            'todayQueue',
            'upcoming',
            'recentPatients',
            'calendarData',
            'today',
            'doctors'
        ));
    }
}

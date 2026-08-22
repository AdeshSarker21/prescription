<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPatients = Patient::count();
        $totalDoctors = User::role('doctor')->count();
        $newDoctorsThisMonth = User::role('doctor')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())->count();
        $pendingAppointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', 'scheduled')
            ->count();

        $monthlyRevenue = Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.billing_cycle', 'monthly')
            ->sum('plans.monthly_price');

        $yearlyRevenue = Subscription::where('status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.billing_cycle', 'yearly')
            ->sum('plans.yearly_price');

        $totalRevenue = $monthlyRevenue + $yearlyRevenue;

        $monthlyRevenueData = [];
        $revenueMonths = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenueMonths[] = $month->format('M');
            $monthlyRevenueData[] = (float) Subscription::where('subscriptions.status', 'active')
                ->whereMonth('subscriptions.created_at', $month->month)
                ->whereYear('subscriptions.created_at', $month->year)
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.billing_cycle', 'monthly')
                ->sum('plans.monthly_price');
        }
        $maxRevenue = max($monthlyRevenueData) ?: 1;

        $recentAppointments = Appointment::with(['doctor', 'patient'])
            ->latest('appointment_date')
            ->take(6)
            ->get();

        $pendingApprovals = User::where('is_approved', false)
            ->whereHas('roles', fn($q) => $q->where('name', 'doctor'))
            ->count();

        $pendingSubscriptions = Subscription::where('status', 'pending')->count();

        $pendingSuggestions = \App\Models\MedicineSuggestion::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalPatients', 'totalDoctors', 'newDoctorsThisMonth',
            'todayAppointments', 'pendingAppointments',
            'totalRevenue', 'monthlyRevenueData', 'revenueMonths', 'maxRevenue',
            'recentAppointments',
            'pendingApprovals', 'pendingSubscriptions', 'pendingSuggestions',
        ));
    }
}

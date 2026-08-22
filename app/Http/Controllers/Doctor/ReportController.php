<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $doctorId = auth()->id();

        $totalPatients = Patient::where('doctor_id', $doctorId)->count();
        $totalPrescriptions = Prescription::where('doctor_id', $doctorId)->count();
        $totalAppointments = Appointment::where('doctor_id', $doctorId)->count();
        $thisMonthCount = Prescription::where('doctor_id', $doctorId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return view('doctor.reports.index', compact(
            'totalPatients', 'totalPrescriptions', 'totalAppointments', 'thisMonthCount'
        ));
    }

    public function patients(): JsonResponse
    {
        $doctorId = auth()->id();

        $total = Patient::where('doctor_id', $doctorId)->count();

        $newThisMonth = Patient::where('doctor_id', $doctorId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $active = Patient::where('doctor_id', $doctorId)
            ->whereIn('id', Prescription::where('doctor_id', $doctorId)
                ->select('patient_id')
                ->distinct()
            )
            ->count();

        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Patient::where('doctor_id', $doctorId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthly[$month->format('M Y')] = $count;
        }

        return response()->json([
            'total' => $total,
            'new_this_month' => $newThisMonth,
            'active' => $active,
            'monthly' => $monthly,
        ]);
    }

    public function prescriptions(): JsonResponse
    {
        $doctorId = auth()->id();

        $total = Prescription::where('doctor_id', $doctorId)->count();

        $thisMonth = Prescription::where('doctor_id', $doctorId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $firstPrescription = Prescription::where('doctor_id', $doctorId)
            ->oldest('created_at')
            ->first();

        $daysSinceFirst = $firstPrescription
            ? max(1, now()->diffInDays($firstPrescription->created_at))
            : 1;

        $avgPerDay = round($total / $daysSinceFirst, 1);

        $topMedicines = PrescriptionItem::whereHas('prescription', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            })
            ->select('medicine_name', DB::raw('COUNT(*) as count'))
            ->groupBy('medicine_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($item) => ['name' => $item->medicine_name, 'count' => $item->count]);

        return response()->json([
            'total' => $total,
            'this_month' => $thisMonth,
            'avg_per_day' => $avgPerDay,
            'top_medicines' => $topMedicines,
        ]);
    }

    public function monthly(): JsonResponse
    {
        $doctorId = auth()->id();

        $patientsTotal = Patient::where('doctor_id', $doctorId)->count();
        $prescriptionsTotal = Prescription::where('doctor_id', $doctorId)->count();
        $appointmentsTotal = Appointment::where('doctor_id', $doctorId)->count();
        $completedTotal = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'completed')
            ->count();

        $monthlyData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $totalActivity = 0;

            $totalActivity += Patient::where('doctor_id', $doctorId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $totalActivity += Prescription::where('doctor_id', $doctorId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $totalActivity += Appointment::where('doctor_id', $doctorId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyData[$month->format('M Y')] = $totalActivity;
        }

        return response()->json([
            'patients' => $patientsTotal,
            'prescriptions' => $prescriptionsTotal,
            'appointments' => $appointmentsTotal,
            'completed' => $completedTotal,
            'monthly_data' => $monthlyData,
        ]);
    }
}

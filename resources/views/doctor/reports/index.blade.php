@extends('doctor.layouts.app')

@section('title', 'Reports')

@section('header', 'Reports')

@section('content')
<div class="space-y-6" x-data="{
    showPatients: false,
    showPrescriptions: false,
    showMonthly: false,
    patientStats: null,
    prescriptionStats: null,
    monthlyStats: null,
    loadingPatients: false,
    loadingPrescriptions: false,
    loadingMonthly: false,
    fetchPatients() {
        this.loadingPatients = true;
        this.showPatients = !this.showPatients;
        if (!this.patientStats) {
            fetch('{{ route('doctor.reports.patients') }}')
                .then(res => res.json())
                .then(data => { this.patientStats = data; })
                .catch(() => { this.patientStats = { error: 'Failed to load data' }; })
                .finally(() => { this.loadingPatients = false; });
        } else {
            this.loadingPatients = false;
        }
    },
    fetchPrescriptions() {
        this.loadingPrescriptions = true;
        this.showPrescriptions = !this.showPrescriptions;
        if (!this.prescriptionStats) {
            fetch('{{ route('doctor.reports.prescriptions') }}')
                .then(res => res.json())
                .then(data => { this.prescriptionStats = data; })
                .catch(() => { this.prescriptionStats = { error: 'Failed to load data' }; })
                .finally(() => { this.loadingPrescriptions = false; });
        } else {
            this.loadingPrescriptions = false;
        }
    },
    fetchMonthly() {
        this.loadingMonthly = true;
        this.showMonthly = !this.showMonthly;
        if (!this.monthlyStats) {
            fetch('{{ route('doctor.reports.monthly') }}')
                .then(res => res.json())
                .then(data => { this.monthlyStats = data; })
                .catch(() => { this.monthlyStats = { error: 'Failed to load data' }; })
                .finally(() => { this.loadingMonthly = false; });
        } else {
            this.loadingMonthly = false;
        }
    }
}">
    {{-- Overview Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Total Patients</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPatients ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Total Prescriptions</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalPrescriptions ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">Total Appointments</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalAppointments ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-500">This Month</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $thisMonthCount ?? 0 }}</p>
        </div>
    </div>

    {{-- Patient Statistics --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <button @click="fetchPatients()" class="w-full px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Patient Statistics</h3>
            <svg class="w-5 h-5 text-gray-500 transition-transform" :class="showPatients ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="showPatients" x-cloak class="px-6 pb-6">
            <div x-show="loadingPatients" class="text-center py-8 text-sm text-gray-500">Loading...</div>
            <div x-show="!loadingPatients && patientStats" class="space-y-6">
                <template x-if="patientStats && !patientStats.error">
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="bg-indigo-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-indigo-600" x-text="patientStats.total || 0"></p>
                                <p class="text-xs text-indigo-600 mt-1">Total Patients</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600" x-text="patientStats.new_this_month || 0"></p>
                                <p class="text-xs text-green-600 mt-1">New This Month</p>
                            </div>
                            <div class="bg-amber-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-amber-600" x-text="patientStats.active || 0"></p>
                                <p class="text-xs text-amber-600 mt-1">Active Patients</p>
                            </div>
                        </div>
                        {{-- Bar Chart --}}
                        <div x-show="patientStats.monthly" class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-3">Patient Registrations (Monthly)</p>
                            <div class="flex items-end gap-2 h-32">
                                <template x-for="(count, month) in patientStats.monthly" :key="month">
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full bg-indigo-500 rounded-t transition-all" :style="'height: ' + (count / Math.max(...Object.values(patientStats.monthly)) * 100) + '%'"></div>
                                        <span class="text-xs text-gray-500" x-text="count"></span>
                                        <span class="text-xs text-gray-400" x-text="month"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="patientStats && patientStats.error" class="text-center py-4 text-sm text-red-500" x-text="patientStats.error"></div>
            </div>
        </div>
    </div>

    {{-- Prescription Reports --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <button @click="fetchPrescriptions()" class="w-full px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Prescription Reports</h3>
            <svg class="w-5 h-5 text-gray-500 transition-transform" :class="showPrescriptions ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="showPrescriptions" x-cloak class="px-6 pb-6">
            <div x-show="loadingPrescriptions" class="text-center py-8 text-sm text-gray-500">Loading...</div>
            <div x-show="!loadingPrescriptions && prescriptionStats" class="space-y-4">
                <template x-if="prescriptionStats && !prescriptionStats.error">
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="bg-purple-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-purple-600" x-text="prescriptionStats.total || 0"></p>
                                <p class="text-xs text-purple-600 mt-1">Total Prescriptions</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-blue-600" x-text="prescriptionStats.this_month || 0"></p>
                                <p class="text-xs text-blue-600 mt-1">This Month</p>
                            </div>
                            <div class="bg-teal-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-teal-600" x-text="prescriptionStats.avg_per_day || 0"></p>
                                <p class="text-xs text-teal-600 mt-1">Avg Per Day</p>
                            </div>
                        </div>
                        <div x-show="prescriptionStats.top_medicines" class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-3">Most Prescribed Medicines</p>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Medicine</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, i) in prescriptionStats.top_medicines" :key="i">
                                        <tr class="border-t border-gray-100">
                                            <td class="px-3 py-2 text-gray-700" x-text="item.name || item.medicine_name"></td>
                                            <td class="px-3 py-2 text-gray-900 font-medium" x-text="item.count"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
                <div x-show="prescriptionStats && prescriptionStats.error" class="text-center py-4 text-sm text-red-500" x-text="prescriptionStats.error"></div>
            </div>
        </div>
    </div>

    {{-- Monthly Activity Report --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <button @click="fetchMonthly()" class="w-full px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Monthly Activity Report</h3>
            <svg class="w-5 h-5 text-gray-500 transition-transform" :class="showMonthly ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="showMonthly" x-cloak class="px-6 pb-6">
            <div x-show="loadingMonthly" class="text-center py-8 text-sm text-gray-500">Loading...</div>
            <div x-show="!loadingMonthly && monthlyStats" class="space-y-4">
                <template x-if="monthlyStats && !monthlyStats.error">
                    <div>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                            <div class="bg-indigo-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-indigo-600" x-text="monthlyStats.patients || 0"></p>
                                <p class="text-xs text-indigo-600 mt-1">New Patients</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600" x-text="monthlyStats.prescriptions || 0"></p>
                                <p class="text-xs text-green-600 mt-1">Prescriptions</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-blue-600" x-text="monthlyStats.appointments || 0"></p>
                                <p class="text-xs text-blue-600 mt-1">Appointments</p>
                            </div>
                            <div class="bg-amber-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-amber-600" x-text="monthlyStats.completed || 0"></p>
                                <p class="text-xs text-amber-600 mt-1">Completed</p>
                            </div>
                        </div>
                        <div x-show="monthlyStats.monthly_data" class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-3">Monthly Trends</p>
                            <div class="flex items-end gap-2 h-32">
                                <template x-for="(item, month) in monthlyStats.monthly_data" :key="month">
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="w-full bg-indigo-500 rounded-t transition-all" :style="'height: ' + (item / Math.max(...Object.values(monthlyStats.monthly_data).map(v => v)) * 100) + '%'"></div>
                                        <span class="text-xs text-gray-500" x-text="item"></span>
                                        <span class="text-xs text-gray-400" x-text="month"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="monthlyStats && monthlyStats.error" class="text-center py-4 text-sm text-red-500" x-text="monthlyStats.error"></div>
            </div>
        </div>
    </div>
</div>
@endsection

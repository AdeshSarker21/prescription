<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorFeatureSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorFeatureSettingController extends Controller
{
    public function index()
    {
        $doctors = User::role('doctor')
            ->with('doctorFeatureSetting')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.doctor-feature-settings.index', compact('doctors'));
    }

    public function update(Request $request, int $doctorId)
    {
        $data = $request->validate([
            'feature_key' => ['required', 'string', Rule::in(DoctorFeatureSetting::ALL_FEATURES)],
            'value' => 'required|in:0,1',
        ]);

        DoctorFeatureSetting::updateOrCreate(
            ['doctor_id' => $doctorId],
            [$data['feature_key'] => $data['value'] === '1']
        );

        $label = DoctorFeatureSetting::getFeatureLabel($data['feature_key']);
        $state = $data['value'] === '1' ? 'enabled' : 'disabled';

        return back()->with('success', "{$label} {$state} for this doctor.");
    }
}

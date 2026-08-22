<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorPrescriptionSetting;
use App\Models\PrescriptionFooter;
use App\Models\PrescriptionHeader;
use App\Models\User;
use Illuminate\Http\Request;

class PrescriptionSettingController extends Controller
{
    public function headers()
    {
        $items = PrescriptionHeader::orderByDesc('id')->paginate(20);
        return view('admin.prescription-settings.headers', compact('items'));
    }

    public function storeHeader(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        PrescriptionHeader::create($data);

        return back()->with('success', 'Header template created successfully.');
    }

    public function updateHeader(Request $request, int $id)
    {
        $item = PrescriptionHeader::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $item->update($data);

        return back()->with('success', 'Header template updated successfully.');
    }

    public function destroyHeader(int $id)
    {
        $item = PrescriptionHeader::findOrFail($id);

        if ($item->doctors()->count() > 0) {
            return back()->with('error', 'Cannot delete: this header is assigned to one or more doctors.');
        }

        $item->delete();

        return back()->with('success', 'Header template deleted successfully.');
    }

    public function footers()
    {
        $items = PrescriptionFooter::orderByDesc('id')->paginate(20);
        return view('admin.prescription-settings.footers', compact('items'));
    }

    public function storeFooter(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        PrescriptionFooter::create($data);

        return back()->with('success', 'Footer template created successfully.');
    }

    public function updateFooter(Request $request, int $id)
    {
        $item = PrescriptionFooter::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $item->update($data);

        return back()->with('success', 'Footer template updated successfully.');
    }

    public function destroyFooter(int $id)
    {
        $item = PrescriptionFooter::findOrFail($id);

        if ($item->doctors()->count() > 0) {
            return back()->with('error', 'Cannot delete: this footer is assigned to one or more doctors.');
        }

        $item->delete();

        return back()->with('success', 'Footer template deleted successfully.');
    }

    public function doctorSettings()
    {
        $doctors = User::role('doctor')->with('prescriptionSetting')->orderBy('name')->paginate(20);
        $headers = PrescriptionHeader::active()->orderBy('name')->get();
        $footers = PrescriptionFooter::active()->orderBy('name')->get();

        return view('admin.prescription-settings.doctors', compact('doctors', 'headers', 'footers'));
    }

    public function updateDoctorSetting(Request $request, int $doctorId)
    {
        $data = $request->validate([
            'header_enabled' => 'nullable|in:0,1',
            'header_id' => 'nullable|exists:prescription_headers,id',
            'footer_enabled' => 'nullable|in:0,1',
            'footer_id' => 'nullable|exists:prescription_footers,id',
        ]);

        DoctorPrescriptionSetting::updateOrCreate(
            ['doctor_id' => $doctorId],
            [
                'header_enabled' => isset($data['header_enabled']),
                'header_id' => $data['header_id'] ?? null,
                'footer_enabled' => isset($data['footer_enabled']),
                'footer_id' => $data['footer_id'] ?? null,
            ]
        );

        return back()->with('success', 'Doctor prescription settings updated.');
    }
}

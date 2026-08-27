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
            'paper_size' => 'nullable|string|in:A4,A5,Letter,Custom',
            'paper_width_mm' => 'nullable|numeric|min:50|max:1000',
            'paper_height_mm' => 'nullable|numeric|min:50|max:1500',
            'header_margin_top_mm' => 'nullable|numeric|min:0|max:100',
            'header_margin_right_mm' => 'nullable|numeric|min:0|max:100',
            'header_margin_bottom_mm' => 'nullable|numeric|min:0|max:100',
            'header_margin_left_mm' => 'nullable|numeric|min:0|max:100',
            'header_padding_top_mm' => 'nullable|numeric|min:0|max:100',
            'header_padding_right_mm' => 'nullable|numeric|min:0|max:100',
            'header_padding_bottom_mm' => 'nullable|numeric|min:0|max:100',
            'header_padding_left_mm' => 'nullable|numeric|min:0|max:100',
            'footer_margin_top_mm' => 'nullable|numeric|min:0|max:100',
            'footer_margin_right_mm' => 'nullable|numeric|min:0|max:100',
            'footer_margin_bottom_mm' => 'nullable|numeric|min:0|max:100',
            'footer_margin_left_mm' => 'nullable|numeric|min:0|max:100',
            'footer_padding_top_mm' => 'nullable|numeric|min:0|max:100',
            'footer_padding_right_mm' => 'nullable|numeric|min:0|max:100',
            'footer_padding_bottom_mm' => 'nullable|numeric|min:0|max:100',
            'footer_padding_left_mm' => 'nullable|numeric|min:0|max:100',
        ]);

        $updateData = [];

        if ($request->has('header_enabled')) {
            $updateData['header_enabled'] = $request->boolean('header_enabled');
        }
        if ($request->has('header_id')) {
            $updateData['header_id'] = $data['header_id'] ?? null;
        }
        if ($request->has('footer_enabled')) {
            $updateData['footer_enabled'] = $request->boolean('footer_enabled');
        }
        if ($request->has('footer_id')) {
            $updateData['footer_id'] = $data['footer_id'] ?? null;
        }

        $layoutFields = [
            'paper_size',
            'paper_width_mm',
            'paper_height_mm',
            'header_margin_top_mm',
            'header_margin_right_mm',
            'header_margin_bottom_mm',
            'header_margin_left_mm',
            'header_padding_top_mm',
            'header_padding_right_mm',
            'header_padding_bottom_mm',
            'header_padding_left_mm',
            'footer_margin_top_mm',
            'footer_margin_right_mm',
            'footer_margin_bottom_mm',
            'footer_margin_left_mm',
            'footer_padding_top_mm',
            'footer_padding_right_mm',
            'footer_padding_bottom_mm',
            'footer_padding_left_mm',
        ];

        foreach ($layoutFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $data[$field] ?? null;
            }
        }

        DoctorPrescriptionSetting::updateOrCreate(
            ['doctor_id' => $doctorId],
            $updateData
        );

        return back()->with('success', 'Doctor prescription settings updated.');
    }
}

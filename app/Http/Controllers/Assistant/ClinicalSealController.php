<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\ClinicalSeal;
use App\Models\User;
use Illuminate\Http\Request;

class ClinicalSealController extends Controller
{
    public function index(Request $request)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();

        $query = ClinicalSeal::whereIn('doctor_id', $doctorIds)
            ->with('doctor:id,name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'inactive') {
                $query->where('status', '!=', 'active');
            }
        }

        if ($doctorId = $request->input('doctor_id')) {
            if (in_array($doctorId, $doctorIds)) {
                $query->where('doctor_id', $doctorId);
            }
        }

        $seals = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();

        return view('assistant.clinical-seals.index', compact('seals', 'doctors'));
    }

    public function create()
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();

        if ($doctors->isEmpty()) {
            return redirect()->route('assistant.clinical-seals.index')
                ->with('error', 'No doctors assigned to you. Contact admin.');
        }

        return view('assistant.clinical-seals.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:2000',
            'details' => 'nullable|string|max:5000',
            'doctor_id' => 'required|integer|exists:users,id',
        ]);

        $doctorIds = auth()->user()->getAccessibleDoctorIds();

        if (!in_array($request->doctor_id, $doctorIds)) {
            return back()->withErrors(['doctor_id' => 'You are not assigned to this doctor.'])->withInput();
        }

        $name = trim($request->input('name'));
        $details = trim($request->input('details', ''));
        $normalizedName = mb_strtolower($name);

        $existing = ClinicalSeal::whereRaw('LOWER(name) = ?', [$normalizedName])
            ->where('doctor_id', $request->doctor_id)
            ->first();

        if ($existing) {
            return back()->withErrors(['name' => 'A clinical seal with this name already exists for this doctor.'])->withInput();
        }

        ClinicalSeal::create([
            'name' => $name,
            'details' => $details ?: null,
            'doctor_id' => $request->doctor_id,
            'created_by' => auth()->id(),
            'status' => 'active',
            'is_active' => true,
            'used_count' => 0,
        ]);

        return redirect()->route('assistant.clinical-seals.index')
            ->with('success', 'Clinical seal created successfully.');
    }

    public function edit($id)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $seal = ClinicalSeal::findOrFail($id);

        if (!in_array($seal->doctor_id, $doctorIds)) {
            abort(403, 'You are not authorized to edit this seal.');
        }

        $doctors = User::role('doctor')->whereIn('id', $doctorIds)->get();

        return view('assistant.clinical-seals.edit', compact('seal', 'doctors'));
    }

    public function update(Request $request, $id)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $seal = ClinicalSeal::findOrFail($id);

        if (!in_array($seal->doctor_id, $doctorIds)) {
            abort(403, 'You are not authorized to edit this seal.');
        }

        $request->validate([
            'name' => 'required|string|max:2000',
            'details' => 'nullable|string|max:5000',
            'doctor_id' => 'required|integer|exists:users,id',
        ]);

        if (!in_array($request->doctor_id, $doctorIds)) {
            return back()->withErrors(['doctor_id' => 'You are not assigned to this doctor.'])->withInput();
        }

        $name = trim($request->input('name'));
        $details = trim($request->input('details', ''));
        $normalizedName = mb_strtolower($name);

        $existing = ClinicalSeal::whereRaw('LOWER(name) = ?', [$normalizedName])
            ->where('doctor_id', $request->doctor_id)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return back()->withErrors(['name' => 'A clinical seal with this name already exists for this doctor.'])->withInput();
        }

        $seal->update([
            'name' => $name,
            'details' => $details ?: null,
            'doctor_id' => $request->doctor_id,
        ]);

        return redirect()->route('assistant.clinical-seals.index')
            ->with('success', 'Clinical seal updated successfully.');
    }

    public function destroy($id)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $seal = ClinicalSeal::findOrFail($id);

        if (!in_array($seal->doctor_id, $doctorIds)) {
            abort(403, 'You are not authorized to delete this seal.');
        }

        if ($seal->used_count > 0) {
            return back()->with('error', 'Cannot delete: this seal has been used ' . $seal->used_count . ' time(s). Consider marking it as inactive instead.');
        }

        $seal->delete();

        return redirect()->route('assistant.clinical-seals.index')
            ->with('success', 'Clinical seal deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $doctorIds = auth()->user()->getAccessibleDoctorIds();
        $seal = ClinicalSeal::findOrFail($id);

        if (!in_array($seal->doctor_id, $doctorIds)) {
            abort(403, 'You are not authorized to modify this seal.');
        }

        $newStatus = $seal->status === 'active' ? 'inactive' : 'active';
        $seal->update([
            'status' => $newStatus,
            'is_active' => $newStatus === 'active',
        ]);

        return back()->with('success', 'Clinical seal status updated to ' . ucfirst($newStatus) . '.');
    }
}

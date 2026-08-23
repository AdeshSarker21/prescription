<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartSerialChamber;
use App\Models\User;
use Illuminate\Http\Request;

class ChamberController extends Controller
{
    public function index()
    {
        $chambers = SmartSerialChamber::with('doctor')->orderBy('doctor_id')->orderBy('name')->get();
        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->orderBy('name')->get();

        return view('admin.chambers.index', compact('chambers', 'doctors'));
    }

    public function create()
    {
        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->orderBy('name')->get();

        return view('admin.chambers.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'chamber_number' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'serial_prefix' => 'nullable|string|max:20',
            'daily_starting_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        SmartSerialChamber::create($request->only([
            'doctor_id', 'name', 'chamber_number', 'is_active',
            'serial_prefix', 'daily_starting_number', 'description',
        ]));

        return redirect()->route('admin.chambers.index')->with('success', 'Chamber created successfully.');
    }

    public function edit(SmartSerialChamber $chamber)
    {
        $doctors = User::whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->orderBy('name')->get();

        return view('admin.chambers.edit', compact('chamber', 'doctors'));
    }

    public function update(Request $request, SmartSerialChamber $chamber)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'chamber_number' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'serial_prefix' => 'nullable|string|max:20',
            'daily_starting_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        $chamber->update($request->only([
            'doctor_id', 'name', 'chamber_number', 'is_active',
            'serial_prefix', 'daily_starting_number', 'description',
        ]));

        return redirect()->route('admin.chambers.index')->with('success', 'Chamber updated successfully.');
    }

    public function destroy(SmartSerialChamber $chamber)
    {
        $chamber->delete();

        return redirect()->route('admin.chambers.index')->with('success', 'Chamber deleted successfully.');
    }
}

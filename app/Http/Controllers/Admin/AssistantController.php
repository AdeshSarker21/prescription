<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorAssistant;
use App\Models\User;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function index()
    {
        $doctors = User::role('doctor')
            ->with('assistants')
            ->orderBy('name')
            ->paginate(20);

        $assistants = User::role('assistant')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.assistants.index', compact('doctors', 'assistants'));
    }

    public function create()
    {
        return view('admin.assistants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'is_approved' => true,
            'status' => 'active',
        ]);

        $user->assignRole('assistant');

        return redirect()->route('admin.assistants.index')
            ->with('success', 'Assistant created successfully.');
    }

    public function edit(User $assistant)
    {
        return view('admin.assistants.edit', compact('assistant'));
    }

    public function update(Request $request, User $assistant)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $assistant->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $assistant->update($data);

        return redirect()->route('admin.assistants.index')
            ->with('success', 'Assistant updated successfully.');
    }

    public function destroy(User $assistant)
    {
        $assistant->delete();

        return redirect()->route('admin.assistants.index')
            ->with('success', 'Assistant deleted successfully.');
    }

    public function assign(int $doctorId)
    {
        $doctor = User::findOrFail($doctorId);
        $assignedIds = $doctor->assistants()->pluck('users.id')->toArray();
        $assistants = User::role('assistant')->orderBy('name')->get();

        return view('admin.assistants.assign', compact('doctor', 'assistants', 'assignedIds'));
    }

    public function storeAssignment(Request $request, int $doctorId)
    {
        $doctor = User::findOrFail($doctorId);

        $request->validate([
            'assistant_ids' => 'required|array|min:1',
            'assistant_ids.*' => 'exists:users,id',
        ]);

        $assistantIds = $request->input('assistant_ids');

        // Verify all selected users have the assistant role
        $validAssistants = User::role('assistant')->whereIn('id', $assistantIds)->pluck('id')->toArray();

        if (count($validAssistants) !== count($assistantIds)) {
            return back()->with('error', 'Selected users must have the assistant role.');
        }

        // Sync assignments
        $doctor->assistants()->sync($validAssistants);

        return back()->with('success', 'Assistants assigned successfully.');
    }

    public function removeAssignment(int $doctorId, int $assistantId)
    {
        DoctorAssistant::where('doctor_id', $doctorId)->where('assistant_id', $assistantId)->delete();

        return back()->with('success', 'Assistant unassigned successfully.');
    }
}

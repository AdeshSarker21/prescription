<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(): View
    {
        $notices = Notice::forDoctor(auth()->id())->latest()->paginate(10);
        return view('doctor.notices.index', compact('notices'));
    }

    public function create(): View
    {
        return view('doctor.notices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'message'  => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['doctor_id'] = auth()->id();
        $data['is_active'] = $request->boolean('is_active');

        Notice::create($data);

        return redirect()->route('doctor.notices.index')
            ->with('success', 'Notice created successfully.');
    }

    public function edit(Notice $notice): View
    {
        if ($notice->doctor_id !== auth()->id()) abort(403);
        return view('doctor.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        if ($notice->doctor_id !== auth()->id()) abort(403);

        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'message'  => 'required|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $notice->update($data);

        return redirect()->route('doctor.notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        if ($notice->doctor_id !== auth()->id()) abort(403);

        $notice->delete();

        return redirect()->route('doctor.notices.index')
            ->with('success', 'Notice deleted successfully.');
    }

    public function toggle(Notice $notice): RedirectResponse
    {
        if ($notice->doctor_id !== auth()->id()) abort(403);

        $notice->update(['is_active' => !$notice->is_active]);

        $status = $notice->is_active ? 'enabled' : 'disabled';
        return redirect()->route('doctor.notices.index')
            ->with('success', "Notice {$status} successfully.");
    }
}

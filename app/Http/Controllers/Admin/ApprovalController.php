<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $pendingUsers = User::where('is_approved', false)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'doctor');
            })
            ->with('roles')
            ->get();

        return view('admin.approvals.index', compact('pendingUsers'));
    }

    public function approve(User $user): RedirectResponse
    {
        $user->update(['is_approved' => true]);

        return redirect()->route('admin.approvals.index')
            ->with('success', "{$user->name} has been approved successfully.");
    }

    public function reject(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.approvals.index')
            ->with('success', "User has been rejected and removed.");
    }
}

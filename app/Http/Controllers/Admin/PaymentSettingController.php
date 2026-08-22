<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    public function index(): View
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        return view('admin.settings.payment', compact('methods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'account_number' => 'required|string|max:255',
            'account_holder' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
        ]);

        $data['sort_order'] = PaymentMethod::max('sort_order') + 1;

        PaymentMethod::create($data);

        return redirect()->route('admin.settings.payment')
            ->with('success', 'Payment method added.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'account_number' => 'required|string|max:255',
            'account_holder' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $paymentMethod->update($data);

        return redirect()->route('admin.settings.payment')
            ->with('success', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->delete();

        return redirect()->route('admin.settings.payment')
            ->with('success', 'Payment method removed.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicineSuggestionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $query = MedicineSuggestion::with(['doctor', 'category']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhereHas('doctor', fn($dq) => $dq->where('name', 'like', "%{$search}%"));
            });
        }

        $suggestions = $query->latest()->paginate(15)->withQueryString();

        return view('admin.medicine-suggestions.index', compact('suggestions'));
    }

    public function edit(MedicineSuggestion $medicine_suggestion): View
    {
        $categories = MedicineCategory::where('is_active', true)->get();

        return view('admin.medicine-suggestions.edit', [
            'suggestion' => $medicine_suggestion,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, MedicineSuggestion $medicine_suggestion): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'strength'     => 'nullable|string|max:100',
            'category_id'  => 'nullable|exists:medicine_categories,id',
            'company_name' => 'nullable|string|max:255',
            'admin_notes'  => 'nullable|string',
        ]);

        $medicine_suggestion->update($data);

        return redirect()->route('admin.medicine-suggestions.index')
            ->with('success', 'Suggestion updated successfully.');
    }

    public function approve(MedicineSuggestion $medicine_suggestion): RedirectResponse
    {
        return DB::transaction(function () use ($medicine_suggestion) {
            $name = trim($medicine_suggestion->name);
            $normalizedName = mb_strtolower($name);

            $duplicate = Medicine::whereRaw("LOWER(COALESCE(brand_name, name)) = ?", [$normalizedName])
                ->orWhereRaw("LOWER(name) = ?", [$normalizedName])
                ->first();

            if ($duplicate) {
                $medicine_suggestion->update([
                    'status' => MedicineSuggestion::STATUS_APPROVED,
                    'medicine_id' => $duplicate->id,
                    'admin_notes' => 'Already exists as medicine #' . $duplicate->id . '. Linked.',
                ]);

                return redirect()->route('admin.medicine-suggestions.index')
                    ->with('success', 'Marked approved. Duplicate medicine already exists (ID: ' . $duplicate->id . '). Linked to suggestion.');
            }

            $medicine = Medicine::create([
                'name'         => $name,
                'brand_name'   => $name,
                'generic_name' => $medicine_suggestion->generic_name,
                'strength'     => $medicine_suggestion->strength,
                'category_id'  => $medicine_suggestion->category_id,
                'company_name' => $medicine_suggestion->company_name,
                'is_global'    => true,
                'created_by'   => $medicine_suggestion->doctor_id,
                'status'       => 'active',
            ]);

            $medicine_suggestion->update([
                'status'      => MedicineSuggestion::STATUS_APPROVED,
                'medicine_id' => $medicine->id,
                'admin_notes' => 'Approved and created as Medicine #' . $medicine->id,
            ]);

            return redirect()->route('admin.medicine-suggestions.index')
                ->with('success', 'Medicine "' . $medicine->name . '" created and suggestion approved.');
        });
    }

    public function reject(MedicineSuggestion $medicine_suggestion): RedirectResponse
    {
        $medicine_suggestion->update(['status' => 'rejected']);

        return redirect()->route('admin.medicine-suggestions.index')
            ->with('success', 'Suggestion rejected.');
    }

    public function destroy(MedicineSuggestion $medicine_suggestion): RedirectResponse
    {
        $medicine_suggestion->delete();

        return redirect()->route('admin.medicine-suggestions.index')
            ->with('success', 'Suggestion deleted permanently.');
    }
}

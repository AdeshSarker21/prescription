<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advice;
use App\Models\AnesthesiaRecord;
use App\Models\ClinicalFeature;
use App\Models\ClinicalSeal;
use App\Models\Complaint;
use App\Models\DrugHistory;
use App\Models\FamilyHistory;
use App\Models\LaboratoryTest;
use App\Models\MedicalHistoryCondition;
use App\Models\MenstrualHistory;
use App\Models\OtNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    private array $modules = [
        'complaints' => [
            'model' => Complaint::class,
            'label' => 'Complaints',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => 'complaint_prescription',
            'usageColumn' => 'complaint_id',
        ],
        'tests' => [
            'model' => LaboratoryTest::class,
            'label' => 'Tests',
            'nameField' => 'test_name',
            'searchFields' => ['test_name'],
            'usageTable' => 'prescription_tests',
            'usageColumn' => 'laboratory_test_id',
        ],
        'medical-histories' => [
            'model' => MedicalHistoryCondition::class,
            'label' => 'Past Medical History',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => 'patient_medical_histories',
            'usageColumn' => 'medical_history_condition_id',
        ],
        'advice' => [
            'model' => Advice::class,
            'label' => 'Advice',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => 'prescription_advices',
            'usageColumn' => 'advice_id',
        ],
        'clinical-seals' => [
            'model' => ClinicalSeal::class,
            'label' => 'Clinical Seals',
            'nameField' => 'name',
            'detailsField' => 'details',
            'searchFields' => ['name', 'details'],
            'usageTable' => 'prescription_items',
            'usageColumn' => 'seal_id',
        ],
        'clinical-features' => [
            'model' => ClinicalFeature::class,
            'label' => 'Clinical Features',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => null,
            'usageColumn' => null,
        ],
        'family-histories' => [
            'model' => FamilyHistory::class,
            'label' => 'Family History',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => null,
            'usageColumn' => null,
        ],
        'menstrual-histories' => [
            'model' => MenstrualHistory::class,
            'label' => 'Menstrual History',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => null,
            'usageColumn' => null,
        ],
        'drug-histories' => [
            'model' => DrugHistory::class,
            'label' => 'Drug History',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => null,
            'usageColumn' => null,
        ],
        'ot-notes' => [
            'model' => OtNote::class,
            'label' => 'OT Note / Procedure Done',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => null,
            'usageColumn' => null,
        ],
        'anesthesia-records' => [
            'model' => AnesthesiaRecord::class,
            'label' => 'Anesthesia',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => null,
            'usageColumn' => null,
        ],
        'procedures' => [
            'model' => \App\Models\Procedure::class,
            'label' => 'Procedures',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => 'prescription_procedures',
            'usageColumn' => 'procedure_id',
        ],
        'treatment-plans' => [
            'model' => \App\Models\TreatmentPlan::class,
            'label' => 'Treatment Plans',
            'nameField' => 'name',
            'searchFields' => ['name'],
            'usageTable' => 'prescription_treatment_plans',
            'usageColumn' => 'treatment_plan_id',
        ],
    ];

    private function getConfig(string $module): array
    {
        if (!isset($this->modules[$module])) {
            abort(404);
        }
        return $this->modules[$module];
    }

    private function getUsageCount(array $config, int $id): int
    {
        if (empty($config['usageTable']) || empty($config['usageColumn'])) {
            return 0;
        }

        return DB::table($config['usageTable'])
            ->where($config['usageColumn'], $id)
            ->count();
    }

    public function index(Request $request, string $module)
    {
        $config = $this->getConfig($module);
        $model = $config['model'];
        $query = $model::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search, $config) {
                foreach ($config['searchFields'] as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'inactive') {
                $query->where('status', '!=', 'active');
            }
        }

        $items = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.master-data.index', compact('items', 'module', 'config'));
    }

    public function create(string $module)
    {
        $config = $this->getConfig($module);
        return view('admin.master-data.create', compact('module', 'config'));
    }

    public function store(Request $request, string $module)
    {
        $config = $this->getConfig($module);
        $nameField = $config['nameField'];
        $detailsField = $config['detailsField'] ?? null;

        $validationRules = [
            $nameField => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ];
        if ($detailsField) {
            $validationRules[$detailsField] = 'nullable|string|max:5000';
        }

        $data = $request->validate($validationRules);

        $name = trim($data[$nameField]);
        $normalizedName = mb_strtolower($name);

        $existing = $config['model']::whereRaw("LOWER({$nameField}) = ?", [$normalizedName])->first();
        if ($existing) {
            return back()->withErrors([$nameField => "A record with this name already exists."])->withInput();
        }

        $createData = [
            $nameField => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'status' => $data['status'] ?? 'active',
            'created_by' => auth()->id(),
        ];
        if ($detailsField && isset($data[$detailsField])) {
            $createData[$detailsField] = trim($data[$detailsField]) ?: null;
        }

        $config['model']::create($createData);

        return redirect()->route('admin.master-data.index', $module)
            ->with('success', ucfirst($config['label']) . ' created successfully.');
    }

    public function edit(string $module, int $id)
    {
        $config = $this->getConfig($module);
        $item = $config['model']::findOrFail($id);
        return view('admin.master-data.edit', compact('item', 'module', 'config'));
    }

    public function update(Request $request, string $module, int $id)
    {
        $config = $this->getConfig($module);
        $nameField = $config['nameField'];
        $detailsField = $config['detailsField'] ?? null;
        $item = $config['model']::findOrFail($id);

        $validationRules = [
            $nameField => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ];
        if ($detailsField) {
            $validationRules[$detailsField] = 'nullable|string|max:5000';
        }

        $data = $request->validate($validationRules);

        $name = trim($data[$nameField]);
        $normalizedName = mb_strtolower($name);

        $existing = $config['model']::whereRaw("LOWER({$nameField}) = ?", [$normalizedName])
            ->where('id', '!=', $id)
            ->first();
        if ($existing) {
            return back()->withErrors([$nameField => "A record with this name already exists."])->withInput();
        }

        $updateData = [
            $nameField => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'status' => $data['status'] ?? $item->status,
        ];
        if ($detailsField) {
            $updateData[$detailsField] = isset($data[$detailsField]) ? trim($data[$detailsField]) ?: null : $item->{$detailsField};
        }

        $item->update($updateData);

        return redirect()->route('admin.master-data.index', $module)
            ->with('success', ucfirst($config['label']) . ' updated successfully.');
    }

    public function destroy(string $module, int $id)
    {
        $config = $this->getConfig($module);
        $item = $config['model']::findOrFail($id);

        $usageCount = $this->getUsageCount($config, $id);
        if ($usageCount > 0) {
            return back()->with('error', "Cannot delete: this record is used in {$usageCount} prescription(s). Consider marking it as inactive instead.");
        }

        if (isset($item->used_count) && $item->used_count > 0) {
            return back()->with('error', "Cannot delete: this record has been used {$item->used_count} time(s). Consider marking it as inactive instead.");
        }

        $item->delete();

        return redirect()->route('admin.master-data.index', $module)
            ->with('success', ucfirst($config['label']) . ' deleted successfully.');
    }

    public function toggleStatus(string $module, int $id)
    {
        $config = $this->getConfig($module);
        $item = $config['model']::findOrFail($id);

        $newStatus = $item->status === 'active' ? 'inactive' : 'active';
        $updateData = ['status' => $newStatus];

        if (isset($item->is_active)) {
            $updateData['is_active'] = $newStatus === 'active';
        }

        $item->update($updateData);

        return back()->with('success', ucfirst($config['label']) . ' status updated to ' . ucfirst($newStatus) . '.');
    }
}

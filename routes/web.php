<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;

Route::get('/', function () {
    return view('landing');
});

Route::get('/dashboard', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->isDoctor()) {
            return redirect()->route('doctor.dashboard');
        }
        if (auth()->user()->isAssistant()) {
            return redirect()->route('assistant.dashboard');
        }
    }
    return redirect()->route('login');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Pending approval page (accessible without auth)
Route::get('/approval-pending', function () {
    return view('auth.pending-approval');
})->name('approval.pending');

// Route::get('/test-print', function () {
//     return 'Working';
// });

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return nl2br(Artisan::output() ?: 'Storage link created successfully.');
});


// Admin routes
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'index'])->name('doctors.index');
    Route::get('/doctors/create', [\App\Http\Controllers\Admin\DoctorController::class, 'create'])->name('doctors.create');
    Route::post('/doctors', [\App\Http\Controllers\Admin\DoctorController::class, 'store'])->name('doctors.store');
    Route::get('/doctors/{user}', [\App\Http\Controllers\Admin\DoctorController::class, 'show'])->name('doctors.show');
    Route::get('/doctors/{user}/edit', [\App\Http\Controllers\Admin\DoctorController::class, 'edit'])->name('doctors.edit');
    Route::patch('/doctors/{user}', [\App\Http\Controllers\Admin\DoctorController::class, 'update'])->name('doctors.update');
    Route::delete('/doctors/{user}', [\App\Http\Controllers\Admin\DoctorController::class, 'destroy'])->name('doctors.destroy');

    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/{user}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::delete('/approvals/{user}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');

    Route::get('/patients', [\App\Http\Controllers\Admin\PatientController::class, 'index'])->name('patients.index');

    Route::get('/subscriptions', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/history', [\App\Http\Controllers\Admin\SubscriptionController::class, 'history'])->name('subscriptions.history');
    Route::get('/subscriptions/{subscription}/edit', [\App\Http\Controllers\Admin\SubscriptionController::class, 'edit'])->name('subscriptions.edit');
    Route::patch('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    Route::patch('/subscriptions/{subscription}/approve', [\App\Http\Controllers\Admin\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
    Route::patch('/subscriptions/{subscription}/reject', [\App\Http\Controllers\Admin\SubscriptionController::class, 'reject'])->name('subscriptions.reject');

    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Admin\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::get('/settings/payment', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'index'])->name('settings.payment');
    Route::post('/settings/payment', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'store'])->name('settings.payment.store');
    Route::patch('/settings/payment/{paymentMethod}', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'update'])->name('settings.payment.update');
    Route::delete('/settings/payment/{paymentMethod}', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'destroy'])->name('settings.payment.destroy');

    Route::get('/plans', [\App\Http\Controllers\Admin\PlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/create', [\App\Http\Controllers\Admin\PlanController::class, 'create'])->name('plans.create');
    Route::post('/plans', [\App\Http\Controllers\Admin\PlanController::class, 'store'])->name('plans.store');
    Route::get('/plans/{plan}/edit', [\App\Http\Controllers\Admin\PlanController::class, 'edit'])->name('plans.edit');
    Route::patch('/plans/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [\App\Http\Controllers\Admin\PlanController::class, 'destroy'])->name('plans.destroy');

    Route::get('/medicines', [\App\Http\Controllers\Admin\MedicineController::class, 'index'])->name('medicines.index');
    Route::get('/medicines/create', [\App\Http\Controllers\Admin\MedicineController::class, 'create'])->name('medicines.create');
    Route::post('/medicines', [\App\Http\Controllers\Admin\MedicineController::class, 'store'])->name('medicines.store');
    Route::get('/medicines/{medicine}', [\App\Http\Controllers\Admin\MedicineController::class, 'show'])->name('medicines.show');
    Route::get('/medicines/{medicine}/edit', [\App\Http\Controllers\Admin\MedicineController::class, 'edit'])->name('medicines.edit');
    Route::patch('/medicines/{medicine}', [\App\Http\Controllers\Admin\MedicineController::class, 'update'])->name('medicines.update');
    Route::delete('/medicines/{medicine}', [\App\Http\Controllers\Admin\MedicineController::class, 'destroy'])->name('medicines.destroy');

    Route::get('/medicines-categories', [\App\Http\Controllers\Admin\MedicineCategoryController::class, 'index'])->name('medicines.categories.index');
    Route::post('/medicines-categories', [\App\Http\Controllers\Admin\MedicineCategoryController::class, 'store'])->name('medicines.categories.store');
    Route::get('/medicines-categories/{category}/edit', [\App\Http\Controllers\Admin\MedicineCategoryController::class, 'edit'])->name('medicines.categories.edit');
    Route::patch('/medicines-categories/{category}', [\App\Http\Controllers\Admin\MedicineCategoryController::class, 'update'])->name('medicines.categories.update');
    Route::delete('/medicines-categories/{category}', [\App\Http\Controllers\Admin\MedicineCategoryController::class, 'destroy'])->name('medicines.categories.destroy');

    // Medicine Suggestions
    Route::get('/medicine-suggestions', [\App\Http\Controllers\Admin\MedicineSuggestionController::class, 'index'])->name('medicine-suggestions.index');
    Route::get('/medicine-suggestions/{medicine_suggestion}/edit', [\App\Http\Controllers\Admin\MedicineSuggestionController::class, 'edit'])->name('medicine-suggestions.edit');
    Route::patch('/medicine-suggestions/{medicine_suggestion}', [\App\Http\Controllers\Admin\MedicineSuggestionController::class, 'update'])->name('medicine-suggestions.update');
    Route::patch('/medicine-suggestions/{medicine_suggestion}/approve', [\App\Http\Controllers\Admin\MedicineSuggestionController::class, 'approve'])->name('medicine-suggestions.approve');
    Route::patch('/medicine-suggestions/{medicine_suggestion}/reject', [\App\Http\Controllers\Admin\MedicineSuggestionController::class, 'reject'])->name('medicine-suggestions.reject');
    Route::delete('/medicine-suggestions/{medicine_suggestion}', [\App\Http\Controllers\Admin\MedicineSuggestionController::class, 'destroy'])->name('medicine-suggestions.destroy');

    // Master Data
    Route::get('/master-data/{module}', [\App\Http\Controllers\Admin\MasterDataController::class, 'index'])->name('master-data.index');
    Route::get('/master-data/{module}/create', [\App\Http\Controllers\Admin\MasterDataController::class, 'create'])->name('master-data.create');
    Route::post('/master-data/{module}', [\App\Http\Controllers\Admin\MasterDataController::class, 'store'])->name('master-data.store');
    Route::get('/master-data/{module}/{id}/edit', [\App\Http\Controllers\Admin\MasterDataController::class, 'edit'])->name('master-data.edit');
    Route::patch('/master-data/{module}/{id}', [\App\Http\Controllers\Admin\MasterDataController::class, 'update'])->name('master-data.update');
    Route::delete('/master-data/{module}/{id}', [\App\Http\Controllers\Admin\MasterDataController::class, 'destroy'])->name('master-data.destroy');
    Route::post('/master-data/{module}/{id}/toggle-status', [\App\Http\Controllers\Admin\MasterDataController::class, 'toggleStatus'])->name('master-data.toggle-status');

    // Prescription Settings
    Route::get('/prescription-settings/headers', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'headers'])->name('prescription-settings.headers');
    Route::post('/prescription-settings/headers', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'storeHeader'])->name('prescription-settings.headers.store');
    Route::patch('/prescription-settings/headers/{id}', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'updateHeader'])->name('prescription-settings.headers.update');
    Route::delete('/prescription-settings/headers/{id}', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'destroyHeader'])->name('prescription-settings.headers.destroy');
    Route::get('/prescription-settings/footers', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'footers'])->name('prescription-settings.footers');
    Route::post('/prescription-settings/footers', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'storeFooter'])->name('prescription-settings.footers.store');
    Route::patch('/prescription-settings/footers/{id}', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'updateFooter'])->name('prescription-settings.footers.update');
    Route::delete('/prescription-settings/footers/{id}', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'destroyFooter'])->name('prescription-settings.footers.destroy');
    Route::get('/prescription-settings/doctors', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'doctorSettings'])->name('prescription-settings.doctors');
    Route::patch('/prescription-settings/doctors/{doctorId}', [\App\Http\Controllers\Admin\PrescriptionSettingController::class, 'updateDoctorSetting'])->name('prescription-settings.doctors.update');

    // SMS Settings
    Route::get('/sms-settings', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'index'])->name('sms-settings.index');
    Route::get('/sms-settings/{doctorId}/edit', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'edit'])->name('sms-settings.edit');
    Route::patch('/sms-settings/{doctorId}', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'update'])->name('sms-settings.update');
    Route::post('/sms-settings/{doctorId}/toggle', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'toggleDoctorSms'])->name('sms-settings.toggle');
    Route::post('/sms-settings/{doctorId}/test', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'testSms'])->name('sms-settings.test');
    Route::get('/sms-settings/logs', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'logs'])->name('sms-settings.logs');
    Route::get('/sms-settings/templates', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'templates'])->name('sms-settings.templates');
    Route::post('/sms-settings/templates', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'storeTemplate'])->name('sms-settings.templates.store');
    Route::patch('/sms-settings/templates/{id}', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'updateTemplate'])->name('sms-settings.templates.update');
    Route::delete('/sms-settings/templates/{id}', [\App\Http\Controllers\Admin\DoctorSmsSettingController::class, 'destroyTemplate'])->name('sms-settings.templates.destroy');

    // Doctor Feature Settings
    Route::get('/doctor-feature-settings', [\App\Http\Controllers\Admin\DoctorFeatureSettingController::class, 'index'])->name('doctor-feature-settings.index');
    Route::patch('/doctor-feature-settings/{doctorId}', [\App\Http\Controllers\Admin\DoctorFeatureSettingController::class, 'update'])->name('doctor-feature-settings.update');

    // Assistant Management
    Route::get('/assistants', [\App\Http\Controllers\Admin\AssistantController::class, 'index'])->name('assistants.index');
    Route::get('/assistants/create', [\App\Http\Controllers\Admin\AssistantController::class, 'create'])->name('assistants.create');
    Route::post('/assistants', [\App\Http\Controllers\Admin\AssistantController::class, 'store'])->name('assistants.store');
    Route::get('/assistants/{assistant}/edit', [\App\Http\Controllers\Admin\AssistantController::class, 'edit'])->name('assistants.edit');
    Route::patch('/assistants/{assistant}', [\App\Http\Controllers\Admin\AssistantController::class, 'update'])->name('assistants.update');
    Route::delete('/assistants/{assistant}', [\App\Http\Controllers\Admin\AssistantController::class, 'destroy'])->name('assistants.destroy');
    Route::get('/assistants/assign/{doctor}', [\App\Http\Controllers\Admin\AssistantController::class, 'assign'])->name('assistants.assign');
    Route::post('/assistants/assign/{doctor}', [\App\Http\Controllers\Admin\AssistantController::class, 'storeAssignment'])->name('assistants.store-assignment');
    Route::delete('/assistants/{doctor}/{assistant}', [\App\Http\Controllers\Admin\AssistantController::class, 'removeAssignment'])->name('assistants.remove-assignment');

    // Admin Profile
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [\App\Http\Controllers\Admin\ProfileController::class, 'changePassword'])->name('profile.changePassword');

    // Module Management
    Route::get('/modules', [\App\Http\Controllers\Admin\ModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules/{module}/toggle-global', [\App\Http\Controllers\Admin\ModuleController::class, 'toggleGlobal'])->name('modules.toggle-global');
    Route::get('/modules/{module}/doctors', [\App\Http\Controllers\Admin\ModuleController::class, 'doctorSettings'])->name('modules.doctors');
    Route::patch('/modules/{module}/doctors/{doctorId}', [\App\Http\Controllers\Admin\ModuleController::class, 'updateDoctorModule'])->name('modules.doctors.update');
    Route::post('/modules/{module}/doctors/bulk-toggle', [\App\Http\Controllers\Admin\ModuleController::class, 'bulkToggleDoctorModule'])->name('modules.doctors.bulk-toggle');
    Route::get('/modules/{module}/users', [\App\Http\Controllers\Admin\ModuleController::class, 'userModules'])->name('modules.users');
    Route::patch('/modules/{module}/users/{userId}', [\App\Http\Controllers\Admin\ModuleController::class, 'toggleUserModule'])->name('modules.users.toggle');

    // Module Permission Management
    Route::get('/modules/{moduleSlug}/permissions', [\App\Http\Controllers\Admin\ModulePermissionController::class, 'index'])->name('modules.permissions.index');
    Route::patch('/modules/{moduleSlug}/permissions/{doctorId}', [\App\Http\Controllers\Admin\ModulePermissionController::class, 'update'])->name('modules.permissions.update');
    Route::patch('/modules/{moduleSlug}/permissions/{doctorId}/toggle-all', [\App\Http\Controllers\Admin\ModulePermissionController::class, 'toggleAll'])->name('modules.permissions.toggle-all');
    Route::post('/modules/{moduleSlug}/permissions/{doctorId}/grant/{permissionName}', [\App\Http\Controllers\Admin\ModulePermissionController::class, 'grant'])->name('modules.permissions.grant');
    Route::post('/modules/{moduleSlug}/permissions/{doctorId}/revoke/{permissionName}', [\App\Http\Controllers\Admin\ModulePermissionController::class, 'revoke'])->name('modules.permissions.revoke');

    // Add-on Management
    Route::get('/addons', [\App\Http\Controllers\Admin\AddonController::class, 'index'])->name('addons.index');
    Route::get('/addons/create', [\App\Http\Controllers\Admin\AddonController::class, 'create'])->name('addons.create');
    Route::post('/addons', [\App\Http\Controllers\Admin\AddonController::class, 'store'])->name('addons.store');
    Route::get('/addons/{addon}/edit', [\App\Http\Controllers\Admin\AddonController::class, 'edit'])->name('addons.edit');
    Route::patch('/addons/{addon}', [\App\Http\Controllers\Admin\AddonController::class, 'update'])->name('addons.update');
    Route::delete('/addons/{addon}', [\App\Http\Controllers\Admin\AddonController::class, 'destroy'])->name('addons.destroy');
});

// Subscription routes (user-facing)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/subscription/plans', [\App\Http\Controllers\SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/{plan}/subscribe', [\App\Http\Controllers\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('/subscription/{plan}/checkout', [\App\Http\Controllers\SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::post('/subscription/payment/success', [\App\Http\Controllers\SubscriptionController::class, 'paymentSuccess'])->name('subscription.payment.success');
    Route::post('/subscription/{subscription}/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

// Doctor routes
// Notifications (no subscription check — needed for reminders)
Route::middleware(['auth', 'verified', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\Doctor\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\Doctor\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Doctor\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Doctor\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Doctor\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Profile (no subscription required)
    Route::get('/profile', [\App\Http\Controllers\Doctor\ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [\App\Http\Controllers\Doctor\ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['put', 'patch', 'post'], '/profile', [\App\Http\Controllers\Doctor\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [\App\Http\Controllers\Doctor\ProfileController::class, 'changePassword'])->name('profile.changePassword');
});

Route::middleware(['auth', 'verified', 'role:doctor', 'subscription'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Doctor\DashboardController::class, 'index'])->name('dashboard');

    // Patients
    Route::get('/patients', [\App\Http\Controllers\Doctor\PatientController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [\App\Http\Controllers\Doctor\PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [\App\Http\Controllers\Doctor\PatientController::class, 'store'])->name('patients.store');
    Route::post('/patients/quick-add', [\App\Http\Controllers\Doctor\PatientController::class, 'quickStore'])->name('patients.quick-add');
    Route::get('/patients/{patient}', [\App\Http\Controllers\Doctor\PatientController::class, 'show'])->name('patients.show');
    Route::get('/patients/{patient}/edit', [\App\Http\Controllers\Doctor\PatientController::class, 'edit'])->name('patients.edit');
    Route::patch('/patients/{patient}', [\App\Http\Controllers\Doctor\PatientController::class, 'update'])->name('patients.update');
    Route::delete('/patients/{patient}', [\App\Http\Controllers\Doctor\PatientController::class, 'destroy'])->name('patients.destroy');
    Route::get('/patients/{patient}/history', [\App\Http\Controllers\Doctor\PatientController::class, 'history'])->name('patients.history');
    Route::post('/patients/{patient}/allergies', [\App\Http\Controllers\Doctor\PatientController::class, 'storeAllergy'])->name('patients.allergies.store');
    Route::patch('/patients/{patient}/allergies/{allergy}', [\App\Http\Controllers\Doctor\PatientController::class, 'updateAllergy'])->name('patients.allergies.update');
    Route::delete('/patients/{patient}/allergies/{allergy}', [\App\Http\Controllers\Doctor\PatientController::class, 'destroyAllergy'])->name('patients.allergies.destroy');
    Route::post('/patients/{patient}/medical-histories', [\App\Http\Controllers\Doctor\PatientController::class, 'storeMedicalHistory'])->name('patients.medical-histories.store');
    Route::patch('/patients/{patient}/medical-histories/{history}', [\App\Http\Controllers\Doctor\PatientController::class, 'updateMedicalHistory'])->name('patients.medical-histories.update');
    Route::delete('/patients/{patient}/medical-histories/{history}', [\App\Http\Controllers\Doctor\PatientController::class, 'destroyMedicalHistory'])->name('patients.medical-histories.destroy');
    Route::post('/patients/{patient}/diagnoses', [\App\Http\Controllers\Doctor\PatientController::class, 'storeDiagnosis'])->name('patients.diagnoses.store');

    // Prescriptions
    Route::get('/prescriptions', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/prescriptions/create', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'create'])->name('prescriptions.create');
    Route::get('/prescriptions/patient-data/{patient}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'getPatientData'])->name('prescriptions.patient-data');
    Route::get('/prescriptions/patient-history/{patient}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'getPatientPrescriptions'])->name('prescriptions.patient-history');
    Route::get('/prescriptions/patient-medical-histories/{patient}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'getPatientMedicalHistories'])->name('prescriptions.patient-medical-histories');
    Route::post('/prescriptions/patient-medical-histories/{patient}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'storePatientMedicalHistory'])->name('prescriptions.patient-medical-histories.store');
    Route::post('/prescriptions', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::get('/prescriptions/{prescription}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'show'])->name('prescriptions.show');
    Route::get('/prescriptions/{prescription}/edit', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'edit'])->name('prescriptions.edit');
    Route::put('/prescriptions/{prescription}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'update'])->name('prescriptions.update');
    Route::delete('/prescriptions/{prescription}', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
    Route::get('/prescriptions/{prescription}/print', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'print'])->name('prescriptions.print');
    Route::patch('/prescriptions/{prescription}/status', [\App\Http\Controllers\Doctor\PrescriptionController::class, 'updateStatus'])->name('prescriptions.status');

    // Medicines
    Route::get('/medicines/search', [\App\Http\Controllers\Doctor\MedicineController::class, 'search'])->name('medicines.search');
    Route::post('/medicines/quick-suggest', [\App\Http\Controllers\Doctor\MedicineController::class, 'quickSuggest'])->name('medicines.quickSuggest');
    Route::get('/medicines', [\App\Http\Controllers\Doctor\MedicineController::class, 'index'])->name('medicines.index');
    Route::get('/medicines/{medicine}', [\App\Http\Controllers\Doctor\MedicineController::class, 'show'])->name('medicines.show');
    Route::get('/medicines/suggest/create', [\App\Http\Controllers\Doctor\MedicineController::class, 'suggest'])->name('medicines.suggest');
    Route::post('/medicines/suggest', [\App\Http\Controllers\Doctor\MedicineController::class, 'storeSuggestion'])->name('medicines.storeSuggestion');
    Route::get('/medicines/suggestions/list', [\App\Http\Controllers\Doctor\MedicineController::class, 'suggestions'])->name('medicines.suggestions');

    // Complaints (AJAX)
    Route::get('/complaints/search', [\App\Http\Controllers\Doctor\ComplaintController::class, 'search'])->name('complaints.search');
    Route::get('/complaints/popular', [\App\Http\Controllers\Doctor\ComplaintController::class, 'popular'])->name('complaints.popular');
    Route::post('/complaints', [\App\Http\Controllers\Doctor\ComplaintController::class, 'store'])->name('complaints.store');

    // Lab Tests (AJAX)
    Route::get('/lab-tests/search', [\App\Http\Controllers\Doctor\LabTestController::class, 'search'])->name('lab-tests.search');
    Route::get('/lab-tests/popular', [\App\Http\Controllers\Doctor\LabTestController::class, 'popular'])->name('lab-tests.popular');
    Route::post('/lab-tests', [\App\Http\Controllers\Doctor\LabTestController::class, 'store'])->name('lab-tests.store');

    // Item Search Modal (AJAX)
    Route::get('/items/{type}/search', [\App\Http\Controllers\Doctor\ItemSearchController::class, 'search'])->name('items.search');
    Route::get('/items/{type}/popular', [\App\Http\Controllers\Doctor\ItemSearchController::class, 'popular'])->name('items.popular');
    Route::get('/items/{type}/recent', [\App\Http\Controllers\Doctor\ItemSearchController::class, 'recent'])->name('items.recent');
    Route::post('/items/{type}/store', [\App\Http\Controllers\Doctor\ItemSearchController::class, 'store'])->name('items.store');
    Route::post('/items/{type}/track-usage', [\App\Http\Controllers\Doctor\ItemSearchController::class, 'trackUsage'])->name('items.track-usage');

    // Feature Popups (AJAX) - Family History, Menstrual History, Drug History, OT Note, Anesthesia
    Route::post('/features/{type}/store', [\App\Http\Controllers\Doctor\FeatureController::class, 'store'])->name('features.store');

    // Clinical Seals (AJAX)
    Route::get('/clinical-seals/search', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'search'])->name('clinical-seals.search');
    Route::get('/clinical-seals/popular', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'popular'])->name('clinical-seals.popular');
    Route::get('/clinical-seals/recent', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'recent'])->name('clinical-seals.recent');
    Route::post('/clinical-seals', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'store'])->name('clinical-seals.store');
    Route::put('/clinical-seals/{clinicalSeal}', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'update'])->name('clinical-seals.update');
    Route::delete('/clinical-seals/{clinicalSeal}', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'destroy'])->name('clinical-seals.destroy');
    Route::post('/clinical-seals/track-usage', [\App\Http\Controllers\Doctor\ClinicalSealController::class, 'trackUsage'])->name('clinical-seals.track-usage');

    // AI Medical Assistant
    Route::get('/ai-assistant', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'index'])->name('ai-assistant');
    Route::post('/ai-assistant/chat', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'chat'])->name('ai-assistant.chat');
    Route::post('/ai-assistant/suggest-diagnosis', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'suggestDiagnosis'])->name('ai-assistant.suggestDiagnosis');
    Route::post('/ai-assistant/check-interactions', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'checkInteractions'])->name('ai-assistant.checkInteractions');
    Route::post('/ai-assistant/suggest-medicines', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'suggestMedicines'])->name('ai-assistant.suggestMedicines');
    Route::post('/ai-assistant/suggest-tests', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'suggestTests'])->name('ai-assistant.suggestTests');
    Route::post('/ai-assistant/analyze-patient', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'analyzePatient'])->name('ai-assistant.analyzePatient');
    Route::post('/ai-assistant/contextual-query', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'contextualQuery'])->name('ai-assistant.contextualQuery');
    Route::post('/ai-assistant/suggest-drug', [\App\Http\Controllers\Doctor\AIAssistantController::class, 'suggestDrug'])->name('ai-assistant.suggestDrug');

    // Appointments
    Route::get('/appointments', [\App\Http\Controllers\Doctor\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [\App\Http\Controllers\Doctor\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [\App\Http\Controllers\Doctor\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/today', [\App\Http\Controllers\Doctor\AppointmentController::class, 'today'])->name('appointments.today');
    Route::get('/appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [\App\Http\Controllers\Doctor\AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::patch('/appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [\App\Http\Controllers\Doctor\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::patch('/appointments/{appointment}/complete', [\App\Http\Controllers\Doctor\AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::patch('/appointments/{appointment}/cancel', [\App\Http\Controllers\Doctor\AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Reports
    Route::get('/reports', [\App\Http\Controllers\Doctor\ReportController::class, 'index'])->name('reports');
    Route::get('/reports/patients-data', [\App\Http\Controllers\Doctor\ReportController::class, 'patients'])->name('reports.patients');
    Route::get('/reports/prescriptions-data', [\App\Http\Controllers\Doctor\ReportController::class, 'prescriptions'])->name('reports.prescriptions');
    Route::get('/reports/monthly-data', [\App\Http\Controllers\Doctor\ReportController::class, 'monthly'])->name('reports.monthly');

    // Settings
    Route::get('/settings', [\App\Http\Controllers\Doctor\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/clinic', [\App\Http\Controllers\Doctor\SettingsController::class, 'updateClinic'])->name('settings.updateClinic');
    Route::post('/settings/hours', [\App\Http\Controllers\Doctor\SettingsController::class, 'updateHours'])->name('settings.updateHours');
    Route::post('/settings/notifications', [\App\Http\Controllers\Doctor\SettingsController::class, 'updateNotifications'])->name('settings.updateNotifications');
    Route::post('/settings/prescription', [\App\Http\Controllers\Doctor\SettingsController::class, 'updatePrescription'])->name('settings.updatePrescription');

    // Subscription
    Route::get('/subscription', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'index'])->name('subscription');
    Route::get('/subscription/plans', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'plans'])->name('subscription.plans');
    Route::post('/subscription/{plan}/subscribe', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
    Route::get('/subscription/{subscription}/bkash-checkout', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'bkashCheckout'])->name('subscription.bkash-checkout');
    Route::post('/subscription/{subscription}/process-payment', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'processPayment'])->name('subscription.process-payment');
    Route::get('/subscription/{subscription}/payment-confirmation', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'paymentConfirmation'])->name('subscription.payment-confirmation');
    Route::match(['post', 'delete'], '/subscription/cancel', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscription/payment-history', [\App\Http\Controllers\Doctor\SubscriptionController::class, 'paymentHistory'])->name('subscription.payment-history');

    // SMS Center
    Route::get('/sms-center', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'index'])->name('sms-center.index');
    Route::get('/sms-center/send', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'sendForm'])->name('sms-center.send');
    Route::post('/sms-center/send', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'send'])->name('sms-center.send.post');
    Route::get('/sms-center/logs', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'logs'])->name('sms-center.logs');
    Route::get('/sms-center/templates', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'templates'])->name('sms-center.templates');
    Route::post('/sms-center/templates', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'storeTemplate'])->name('sms-center.templates.store');
    Route::patch('/sms-center/templates/{id}', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'updateTemplate'])->name('sms-center.templates.update');
    Route::delete('/sms-center/templates/{id}', [\App\Http\Controllers\Doctor\SmsCenterController::class, 'destroyTemplate'])->name('sms-center.templates.destroy');

    // Smart Serial Queue
    Route::middleware(['module:smart_serial', 'smart_serial.access'])->prefix('smart-serial')->name('smart-serial.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'index'])->name('index');
        Route::post('/start', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'startSession'])->name('start');
        Route::patch('/{session}/close', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'closeSession'])->name('close');
        Route::patch('/{session}/pause', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'pauseSession'])->name('pause');
        Route::patch('/{session}/resume', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'resumeSession'])->name('resume');
        Route::post('/add-patient', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'addPatient'])->name('add-patient');
        Route::patch('/{session}/call-next', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'callNext'])->name('call-next');
        Route::patch('/queue/{queueId}/call', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'callPatient'])->name('call-patient');
        Route::patch('/queue/{queueId}/start-consultation', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'startConsultation'])->name('start-consultation');
        Route::patch('/queue/{queueId}/complete', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'complete'])->name('complete');
        Route::delete('/queue/{queueId}/cancel', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'cancel'])->name('cancel');
        Route::patch('/queue/{queueId}/no-show', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'noShow'])->name('no-show');
        Route::patch('/queue/{queueId}/recall', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'recall'])->name('recall');
        Route::patch('/queue/{queueId}/skip', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'skip'])->name('skip');
        Route::patch('/queue/{queueId}/emergency', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'emergency'])->name('emergency');
        Route::get('/{session}/status', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'queueStatus'])->name('queue-status');
        Route::get('/settings', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Doctor\SmartSerialController::class, 'updateSettings'])->name('settings.update');
    });

    // Add-on Management
    Route::get('/addons', [\App\Http\Controllers\Doctor\AddonController::class, 'index'])->name('addons.index');
    Route::post('/addons/{addon}/purchase', [\App\Http\Controllers\Doctor\AddonController::class, 'purchase'])->name('addons.purchase');
    Route::get('/addons/{subscription}/checkout', [\App\Http\Controllers\Doctor\AddonController::class, 'checkout'])->name('addons.checkout');
    Route::post('/addons/{subscription}/process-payment', [\App\Http\Controllers\Doctor\AddonController::class, 'processPayment'])->name('addons.process-payment');
    Route::get('/addons/{subscription}/confirmation', [\App\Http\Controllers\Doctor\AddonController::class, 'confirmation'])->name('addons.confirmation');
    Route::post('/addons/{subscription}/cancel', [\App\Http\Controllers\Doctor\AddonController::class, 'cancel'])->name('addons.cancel');
});

// Assistant routes
Route::middleware(['auth', 'verified', 'role:assistant', 'assistant.access'])->prefix('assistant')->name('assistant.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Assistant\DashboardController::class, 'index'])->name('dashboard');

    // Appointments
    Route::get('/appointments', [\App\Http\Controllers\Assistant\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [\App\Http\Controllers\Assistant\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [\App\Http\Controllers\Assistant\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [\App\Http\Controllers\Assistant\AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [\App\Http\Controllers\Assistant\AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::patch('/appointments/{appointment}', [\App\Http\Controllers\Assistant\AppointmentController::class, 'update'])->name('appointments.update');
    Route::patch('/appointments/{appointment}/reschedule', [\App\Http\Controllers\Assistant\AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::patch('/appointments/{appointment}/cancel', [\App\Http\Controllers\Assistant\AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::patch('/appointments/{appointment}/complete', [\App\Http\Controllers\Assistant\AppointmentController::class, 'complete'])->name('appointments.complete');

    // Patients
    Route::get('/patients/create', [\App\Http\Controllers\Assistant\PatientController::class, 'create'])->name('patients.create');
    Route::post('/patients', [\App\Http\Controllers\Assistant\PatientController::class, 'store'])->name('patients.store');
    Route::get('/patients/search', [\App\Http\Controllers\Assistant\PatientController::class, 'search'])->name('patients.search');

    // Doctor availability
    Route::get('/doctor/{doctor}/availability', [\App\Http\Controllers\Assistant\AppointmentController::class, 'availability'])->name('doctor.availability');

    // Clinical Seals
    Route::get('/clinical-seals', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'index'])->name('clinical-seals.index');
    Route::get('/clinical-seals/create', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'create'])->name('clinical-seals.create');
    Route::post('/clinical-seals', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'store'])->name('clinical-seals.store');
    Route::get('/clinical-seals/{id}/edit', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'edit'])->name('clinical-seals.edit');
    Route::patch('/clinical-seals/{id}', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'update'])->name('clinical-seals.update');
    Route::delete('/clinical-seals/{id}', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'destroy'])->name('clinical-seals.destroy');
    Route::post('/clinical-seals/{id}/toggle-status', [\App\Http\Controllers\Assistant\ClinicalSealController::class, 'toggleStatus'])->name('clinical-seals.toggle-status');

    // Smart Serial Queue
    Route::middleware(['module:smart_serial', 'smart_serial.access'])->prefix('smart-serial')->name('smart-serial.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'index'])->name('index');
        Route::post('/start', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'startSession'])->name('start');
        Route::patch('/{session}/close', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'closeSession'])->name('close');
        Route::patch('/{session}/pause', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'pauseSession'])->name('pause');
        Route::patch('/{session}/resume', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'resumeSession'])->name('resume');
        Route::post('/add-patient', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'addPatient'])->name('add-patient');
        Route::patch('/{session}/call-next', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'callNext'])->name('call-next');
        Route::patch('/queue/{queueId}/call', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'callPatient'])->name('call-patient');
        Route::patch('/queue/{queueId}/start-consultation', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'startConsultation'])->name('start-consultation');
        Route::patch('/queue/{queueId}/complete', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'complete'])->name('complete');
        Route::delete('/queue/{queueId}/cancel', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'cancel'])->name('cancel');
        Route::patch('/queue/{queueId}/no-show', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'noShow'])->name('no-show');
        Route::patch('/queue/{queueId}/recall', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'recall'])->name('recall');
        Route::patch('/queue/{queueId}/skip', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'skip'])->name('skip');
        Route::patch('/queue/{queueId}/emergency', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'emergency'])->name('emergency');
        Route::get('/{session}/status', [\App\Http\Controllers\Assistant\SmartSerialController::class, 'queueStatus'])->name('queue-status');
    });

    // Notifications
    Route::post('/notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.markAllRead');
});

// Assistant profile (no assistant.access required)
Route::middleware(['auth', 'verified', 'role:assistant'])->prefix('assistant')->name('assistant.')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\Assistant\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [\App\Http\Controllers\Assistant\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [\App\Http\Controllers\Assistant\ProfileController::class, 'changePassword'])->name('profile.changePassword');
});

// Locale switch
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'bn'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('language.switch');

// Register route
Route::post('/register', [RegisterController::class, 'register']);

require __DIR__.'/auth.php';

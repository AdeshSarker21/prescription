@extends('doctor.layouts.app')

@section('title', 'Settings')

@section('header', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Clinic Information --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Clinic Information</h3>
        <form method="POST" action="{{ route('doctor.settings.updateClinic') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="clinic_name" value="Clinic Name" />
                    <x-text-input id="clinic_name" name="clinic_name" type="text" class="mt-1 block w-full" :value="old('clinic_name', auth()->user()->clinic_name)" placeholder="Your Clinic Name" />
                    <x-input-error :messages="$errors->get('clinic_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="clinic_phone" value="Clinic Phone" />
                    <x-text-input id="clinic_phone" name="clinic_phone" type="text" class="mt-1 block w-full" :value="old('clinic_phone', auth()->user()->clinic_phone ?? '')" />
                    <x-input-error :messages="$errors->get('clinic_phone')" class="mt-1" />
                </div>
            </div>
            <div class="mt-6">
                <x-input-label for="clinic_address" value="Clinic Address" />
                <textarea id="clinic_address" name="clinic_address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('clinic_address', auth()->user()->clinic_address ?? auth()->user()->address) }}</textarea>
                <x-input-error :messages="$errors->get('clinic_address')" class="mt-1" />
            </div>
            <div class="mt-6">
                <x-primary-button>Update Clinic Info</x-primary-button>
            </div>
        </form>
    </div>

    {{-- Working Hours --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Working Hours</h3>
        <form method="POST" action="{{ route('doctor.settings.updateHours') }}">
            @csrf
            <div>
                <x-input-label for="visiting_hours" value="Visiting / Working Hours" />
                <textarea id="visiting_hours" name="visiting_hours" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Monday - Friday: 9:00 AM - 5:00 PM&#10;Saturday: 10:00 AM - 2:00 PM&#10;Sunday: Closed">{{ old('visiting_hours', auth()->user()->visiting_hours) }}</textarea>
                <x-input-error :messages="$errors->get('visiting_hours')" class="mt-1" />
            </div>
            <div class="mt-6">
                <x-primary-button>Update Hours</x-primary-button>
            </div>
        </form>
    </div>

    {{-- Prescription Layout Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="chambersHandler()">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Prescription Layout</h3>
        <form method="POST" action="{{ route('doctor.settings.updatePrescription') }}" @submit="serializeChambers()">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="designation_title" value="Designation Title" />
                    <x-text-input id="designation_title" name="designation_title" type="text" class="mt-1 block w-full" :value="old('designation_title', auth()->user()->designation_title)" placeholder="e.g. Professor & Head of the Dept." />
                    <x-input-error :messages="$errors->get('designation_title')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="affiliated_hospital" value="Affiliated Hospital / Medical College" />
                    <x-text-input id="affiliated_hospital" name="affiliated_hospital" type="text" class="mt-1 block w-full" :value="old('affiliated_hospital', auth()->user()->affiliated_hospital)" placeholder="e.g. Rangpur Medical College & Hospital" />
                    <x-input-error :messages="$errors->get('affiliated_hospital')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="sub_specialties" value="Sub-Specialties / Core Expertise" />
                    <x-text-input id="sub_specialties" name="sub_specialties" type="text" class="mt-1 block w-full" :value="old('sub_specialties', auth()->user()->sub_specialties ? implode(', ', auth()->user()->sub_specialties) : '')" placeholder="Comma-separated, e.g. Psychiatry, Neurology" />
                    <x-input-error :messages="$errors->get('sub_specialties')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="emergency_contact" value="Emergency Contact / WhatsApp" />
                    <x-text-input id="emergency_contact" name="emergency_contact" type="text" class="mt-1 block w-full" :value="old('emergency_contact', auth()->user()->emergency_contact)" placeholder="e.g. 01701-067119" />
                    <x-input-error :messages="$errors->get('emergency_contact')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="prescription_heading" value="Prescription Heading" />
                    <x-text-input id="prescription_heading" name="prescription_heading" type="text" class="mt-1 block w-full" :value="old('prescription_heading', auth()->user()->prescription_heading)" placeholder="e.g. DOCTOR'S PRESCRIPTION" />
                    <x-input-error :messages="$errors->get('prescription_heading')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="prescription_slogan" value="Prescription Slogan / Tagline" />
                    <x-text-input id="prescription_slogan" name="prescription_slogan" type="text" class="mt-1 block w-full" :value="old('prescription_slogan', auth()->user()->prescription_slogan)" placeholder='e.g. "Beautiful Mind, Beautiful Life"' />
                    <x-input-error :messages="$errors->get('prescription_slogan')" class="mt-1" />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label value="Chambers" />
                <div class="space-y-4 mt-2">
                    <template x-for="(chamber, i) in chambers" :key="i">
                        <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-gray-700" x-text="'Chamber ' + (i + 1)"></span>
                                <button type="button" @click="removeChamber(i)" class="text-red-500 hover:text-red-700 text-sm font-medium">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Chamber Name</label>
                                    <input type="text" x-model="chamber.name" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                    <input type="text" x-model="chamber.phone" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Booking Hotline</label>
                                    <input type="text" x-model="chamber.booking_hotline" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Closed Days</label>
                                    <input type="text" x-model="chamber.closed_days" placeholder="e.g. Thu-Fri" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                                    <input type="text" x-model="chamber.address" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Visiting Hours</label>
                                    <input type="text" x-model="chamber.hours" placeholder="e.g. 4PM - 10PM" class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addChamber()" class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    + Add Chamber
                </button>
                <input type="hidden" name="chambers" id="chambers-json" value="">
                <x-input-error :messages="$errors->get('chambers')" class="mt-1" />
            </div>

            <div class="mt-6">
                <x-primary-button>Update Prescription Layout</x-primary-button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function chambersHandler() {
            const existing = @json(auth()->user()->chambers ?? []);
            return {
                chambers: existing.length ? existing : [],
                addChamber() {
                    this.chambers.push({ name: '', address: '', phone: '', hours: '', closed_days: '', booking_hotline: '' });
                },
                removeChamber(i) {
                    this.chambers.splice(i, 1);
                },
                serializeChambers() {
                    document.getElementById('chambers-json').value = JSON.stringify(this.chambers);
                }
            };
        }
    </script>
    @endpush

    {{-- Notification Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{
        emailNotifications: {{ json_encode(auth()->user()->notification_settings['email'] ?? true) }},
        smsNotifications: {{ json_encode(auth()->user()->notification_settings['sms'] ?? false) }},
        appointmentReminders: {{ json_encode(auth()->user()->notification_settings['appointment_reminders'] ?? true) }},
        prescriptionUpdates: {{ json_encode(auth()->user()->notification_settings['prescription_updates'] ?? false) }},
        saveNotifications() {
            $refs.emailNotif.value = this.emailNotifications ? '1' : '0';
            $refs.smsNotif.value = this.smsNotifications ? '1' : '0';
            $refs.apptRemind.value = this.appointmentReminders ? '1' : '0';
            $refs.prescUpdate.value = this.prescriptionUpdates ? '1' : '0';
            $refs.notifForm.submit();
        }
    }">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Settings</h3>
        <form method="POST" action="{{ route('doctor.settings.updateNotifications') }}" x-ref="notifForm">
            @csrf
            <input type="hidden" name="email_notifications" x-ref="emailNotif" value="1">
            <input type="hidden" name="sms_notifications" x-ref="smsNotif" value="0">
            <input type="hidden" name="appointment_reminders" x-ref="apptRemind" value="1">
            <input type="hidden" name="prescription_updates" x-ref="prescUpdate" value="0">

            <div class="space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Email Notifications</p>
                        <p class="text-xs text-gray-500">Receive notifications via email</p>
                    </div>
                    <button type="button" @click="emailNotifications = !emailNotifications" :class="emailNotifications ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="emailNotifications ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">SMS Notifications</p>
                        <p class="text-xs text-gray-500">Receive notifications via SMS</p>
                    </div>
                    <button type="button" @click="smsNotifications = !smsNotifications" :class="smsNotifications ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="smsNotifications ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Appointment Reminders</p>
                        <p class="text-xs text-gray-500">Get reminded about upcoming appointments</p>
                    </div>
                    <button type="button" @click="appointmentReminders = !appointmentReminders" :class="appointmentReminders ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="appointmentReminders ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Prescription Updates</p>
                        <p class="text-xs text-gray-500">Get notified when prescriptions are updated</p>
                    </div>
                    <button type="button" @click="prescriptionUpdates = !prescriptionUpdates" :class="prescriptionUpdates ? 'bg-indigo-600' : 'bg-gray-200'" class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span :class="prescriptionUpdates ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>
            </div>
            <div class="mt-6">
                <x-primary-button type="button" x-on:click="saveNotifications()">Save Notification Settings</x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection

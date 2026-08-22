@extends('admin.layouts.app')

@section('title', __('Add Doctor - Admin'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.doctors.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white/70 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to Doctors') }}
        </a>
        <h1 class="text-2xl font-bold text-white/90">{{ __('Add Doctor') }}</h1>
        <p class="text-sm text-white/50 mt-1">{{ __('Create a new doctor account.') }}</p>
    </div>

    <div class="max-w-3xl glass-card-static p-6" x-data="chambersHandler()">
        <form method="POST" action="{{ route('admin.doctors.store') }}" enctype="multipart/form-data" @submit="serializeChambers()">
            @csrf

            {{-- Avatar --}}
            <div class="mb-6 flex items-center gap-6">
                <div class="shrink-0">
                    <div class="w-20 h-20 rounded-full bg-indigo-500/20 flex items-center justify-center text-2xl font-bold text-indigo-400 border border-white/5">
                        ?
                    </div>
                </div>
                <div class="flex-1">
                    <label for="avatar" class="block text-sm font-medium text-white/70 mb-1">{{ __('Profile Photo') }}</label>
                    <input id="avatar" type="file" name="avatar" accept="image/*"
                        class="block w-full text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-500/20 file:text-indigo-400 hover:file:bg-indigo-500/30">
                    <p class="text-xs text-white/40 mt-1">{{ __('JPEG, PNG, JPG, GIF, or WebP. Max 2MB.') }}</p>
                    @error('avatar') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <hr class="mb-5 border-white/5">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-white/70 mb-1">{{ __('Full Name') }} <span class="text-xs text-white/40">(English)</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required
                        class="w-full glass-input" placeholder="Full Name">
                    @error('name') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="name_bn" class="block text-sm font-medium text-white/70 mb-1">{{ __('Full Name') }} <span class="text-xs text-white/40">(বাংলা)</span></label>
                    <input id="name_bn" type="text" name="name_bn" value="{{ old('name_bn') }}"
                        placeholder="যেমন: ডা. পুরো নাম"
                        class="w-full glass-input">
                    @error('name_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="w-full glass-input" placeholder="Enter Email Address">
                    @error('email') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
                    <input id="password" type="password" name="password" required
                        class="w-full glass-input" placeholder="*******">
                    @error('password') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full glass-input" placeholder="+8801700000000">
                    @error('phone') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- License Number --}}
                <div>
                    <label for="license_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('License Number') }}</label>
                    <input id="license_number" type="text" name="license_number" value="{{ old('license_number') }}"
                        class="w-full glass-input" placeholder="Enter License Number">
                    @error('license_number') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Experience Years --}}
                <div>
                    <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Experience (years)') }}</label>
                    <input id="experience_years" type="number" name="experience_years" value="{{ old('experience_years') }}" min="0" max="100"
                        class="w-full glass-input" placeholder="Enter Experience">
                    @error('experience_years') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Specialization --}}
                <div>
                    <label for="specialization" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Specialization') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="specialization" type="text" name="specialization" value="{{ old('specialization') }}"
                        class="w-full glass-input" placeholder="Enter Specialization">
                    @error('specialization') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="specialization_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Specialization') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="specialization_bn" type="text" name="specialization_bn" value="{{ old('specialization_bn') }}"
                        placeholder="যেমন: মানসিক রোগ বিশেষজ্ঞ"
                        class="w-full glass-input">
                    @error('specialization_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Qualification --}}
                <div>
                    <label for="qualification" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Qualification') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="qualification" type="text" name="qualification" value="{{ old('qualification') }}"
                        class="w-full glass-input" placeholder="Enter Qualification">
                    @error('qualification') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="qualification_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Qualification') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="qualification_bn" type="text" name="qualification_bn" value="{{ old('qualification_bn') }}"
                        placeholder="যেমন: এমবিবিএস, এফসিপিএস (মনোরোগবিদ্যা)"
                        class="w-full glass-input">
                    @error('qualification_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Designation Title --}}
                <div>
                    <label for="designation_title" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Designation Title') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="designation_title" type="text" name="designation_title" value="{{ old('designation_title') }}"
                        placeholder="{{ __('e.g. Professor & Head of the Dept.') }}"
                        class="w-full glass-input">
                    @error('designation_title') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="designation_title_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Designation Title') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="designation_title_bn" type="text" name="designation_title_bn" value="{{ old('designation_title_bn') }}"
                        placeholder="যেমন: অধ্যাপক ও বিভাগীয় প্রধান"
                        class="w-full glass-input">
                    @error('designation_title_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Affiliated Hospital --}}
                <div>
                    <label for="affiliated_hospital" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Affiliated Hospital / Medical College') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="affiliated_hospital" type="text" name="affiliated_hospital" value="{{ old('affiliated_hospital') }}"
                        placeholder="{{ __('e.g. Rangpur Medical College & Hospital') }}"
                        class="w-full glass-input">
                    @error('affiliated_hospital') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="affiliated_hospital_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Affiliated Hospital / Medical College') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="affiliated_hospital_bn" type="text" name="affiliated_hospital_bn" value="{{ old('affiliated_hospital_bn') }}"
                        placeholder="যেমন: রংপুর মেডিকেল কলেজ ও হাসপাতাল"
                        class="w-full glass-input">
                    @error('affiliated_hospital_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Clinic Name --}}
                <div>
                    <label for="clinic_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Clinic Name') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="clinic_name" type="text" name="clinic_name" value="{{ old('clinic_name') }}"
                        class="w-full glass-input" placeholder="Enter Clinic Name">
                    @error('clinic_name') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="clinic_name_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Clinic Name') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="clinic_name_bn" type="text" name="clinic_name_bn" value="{{ old('clinic_name_bn') }}"
                        placeholder="যেমন: সুস্থতা ক্লিনিক"
                        class="w-full glass-input">
                    @error('clinic_name_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Sub-Specialties --}}
                <div>
                    <label for="sub_specialties" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sub-Specialties / Core Expertise') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="sub_specialties" type="text" name="sub_specialties" value="{{ old('sub_specialties') }}"
                        placeholder="{{ __('Comma-separated, e.g. Psychiatry, Neurology') }}"
                        class="w-full glass-input">
                    @error('sub_specialties') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sub_specialties_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sub-Specialties / Core Expertise') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="sub_specialties_bn" type="text" name="sub_specialties_bn" value="{{ old('sub_specialties_bn') }}"
                        placeholder="কমা দ্বারা পৃথক, যেমন: সাইকিয়াট্রি, নিউরোলজি"
                        class="w-full glass-input">
                    @error('sub_specialties_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Emergency Contact --}}
                <div>
                    <label for="emergency_contact" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Emergency Contact / WhatsApp') }}</label>
                    <input id="emergency_contact" type="text" name="emergency_contact" value="{{ old('emergency_contact') }}"
                        placeholder="{{ __('e.g. 01701-067119') }}"
                        class="w-full glass-input">
                    @error('emergency_contact') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="emergency_contact_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Emergency Contact / WhatsApp') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="emergency_contact_bn" type="text" name="emergency_contact_bn" value="{{ old('emergency_contact_bn') }}"
                        placeholder="যেমন: ০১৭০১-০৬৭১১৯"
                        class="w-full glass-input">
                    @error('emergency_contact_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Prescription Heading --}}
                <div>
                    <label for="prescription_heading" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Prescription Heading') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="prescription_heading" type="text" name="prescription_heading" value="{{ old('prescription_heading') }}"
                        placeholder="{{ __('e.g. DOCTOR\'S PRESCRIPTION') }}"
                        class="w-full glass-input">
                    @error('prescription_heading') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="prescription_heading_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Prescription Heading') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="prescription_heading_bn" type="text" name="prescription_heading_bn" value="{{ old('prescription_heading_bn') }}"
                        placeholder="যেমন: ডাক্তারের প্রেসক্রিপশন"
                        class="w-full glass-input">
                    @error('prescription_heading_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Prescription Slogan --}}
                <div>
                    <label for="prescription_slogan" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Prescription Slogan / Tagline') }} <span class="text-xs text-gray-400">(English)</span></label>
                    <input id="prescription_slogan" type="text" name="prescription_slogan" value="{{ old('prescription_slogan') }}"
                        placeholder="{{ __('e.g. \"Beautiful Mind, Beautiful Life\"') }}"
                        class="w-full glass-input">
                    @error('prescription_slogan') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="prescription_slogan_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Prescription Slogan / Tagline') }} <span class="text-xs text-gray-400">(বাংলা)</span></label>
                    <input id="prescription_slogan_bn" type="text" name="prescription_slogan_bn" value="{{ old('prescription_slogan_bn') }}"
                        placeholder='যেমন: "সুন্দর মন, সুন্দর জীবন"'
                        class="w-full glass-input">
                    @error('prescription_slogan_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Chambers --}}
            <div class="mt-5">
                <label class="block text-sm font-medium text-white/70 mb-2">{{ __('Chambers') }}</label>
                <div class="space-y-4">
                    <template x-for="(chamber, i) in chambers" :key="i">
                        <div class="p-4 border border-white/5 rounded-lg bg-white/10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-semibold text-white/70" x-text="'{{ __('Chamber') }} ' + (i + 1)"></span>
                                <button type="button" @click="removeChamber(i)" class="text-red-400 hover:text-red-300 text-sm font-medium">{{ __('Remove') }}</button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-white/60 mb-1">{{ __('Chamber Name') }}</label>
                                    <input type="text" x-model="chamber.name" class="w-full glass-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-white/60 mb-1">{{ __('Phone') }}</label>
                                    <input type="text" x-model="chamber.phone" class="w-full glass-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-white/60 mb-1">{{ __('Booking Hotline') }}</label>
                                    <input type="text" x-model="chamber.booking_hotline" class="w-full glass-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-white/60 mb-1">{{ __('Closed Days') }}</label>
                                    <input type="text" x-model="chamber.closed_days" placeholder="{{ __('e.g. Thu-Fri') }}" class="w-full glass-input">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-white/60 mb-1">{{ __('Address') }}</label>
                                    <input type="text" x-model="chamber.address" class="w-full glass-input">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-white/60 mb-1">{{ __('Visiting Hours') }}</label>
                                    <input type="text" x-model="chamber.hours" placeholder="{{ __('e.g. 4PM - 10PM') }}" class="w-full glass-input">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addChamber()" class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300 font-medium">
                    {{ __('+ Add Chamber') }}
                </button>
                <input type="hidden" name="chambers" id="chambers-json" value="">
                @error('chambers') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Address (full width) --}}
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="address" class="block text-sm font-medium text-white/70 mb-1">{{ __('Address') }} <span class="text-xs text-white/40">(English)</span></label>
                    <textarea id="address" name="address" rows="2"
                        class="w-full glass-input">{{ old('address') }}</textarea>
                    @error('address') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="address_bn" class="block text-sm font-medium text-white/70 mb-1">{{ __('Address') }} <span class="text-xs text-white/40">(বাংলা)</span></label>
                    <textarea id="address_bn" name="address_bn" rows="2"
                        class="w-full glass-input">{{ old('address_bn') }}</textarea>
                    @error('address_bn') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Visiting Hours (full width) --}}
            <div class="mt-5">
                <label for="visiting_hours" class="block text-sm font-medium text-white/70 mb-1">{{ __('Visiting Hours') }}</label>
                <input id="visiting_hours" type="text" name="visiting_hours" value="{{ old('visiting_hours') }}" placeholder="{{ __('e.g. Mon-Fri 9:00 AM - 5:00 PM') }}"
                    class="w-full glass-input">
                @error('visiting_hours') <p class="text-sm text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-white/5">
                <button type="submit" class="btn-gradient">
                    {{ __('Create Doctor') }}
                </button>
                <a href="{{ route('admin.doctors.index') }}" class="btn-outline-glass">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection

<script>
    function chambersHandler() {
        return {
            chambers: [],
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

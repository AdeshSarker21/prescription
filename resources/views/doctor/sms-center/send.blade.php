@extends('doctor.layouts.app')

@section('title', 'Send SMS')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--text-primary);">Send SMS</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted);">Send SMS to one or multiple patients</p>
        </div>
        <a href="{{ route('doctor.sms-center.index') }}" class="btn-outline-glass px-4 py-2 text-sm">Back</a>
    </div>

    @if(session('success'))
        <div data-flash-success="{{ session('success') }}"></div>
    @endif
    @if(session('error'))
        <div data-flash-error="{{ session('error') }}"></div>
    @endif

    @if(!$setting || !$setting->sms_enabled)
        <div class="glass-card-static p-6 text-center" style="color:var(--text-muted);">
            <p>SMS service is not enabled. Contact admin.</p>
        </div>
    @else
        <form method="POST" action="{{ route('doctor.sms-center.send') }}" id="send-sms-form">
            @csrf

            {{-- Template Selector --}}
            <div class="glass-card-static p-4 mb-4">
                <label class="block text-sm font-medium mb-2" style="color:var(--text-primary);">Use Template (Optional)</label>
                <select id="template-select" class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm" onchange="loadTemplate(this.value)">
                    <option value="">-- Write custom message --</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" data-message="{{ $template->message }}">{{ $template->name }} ({{ ucfirst($template->type) }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Message --}}
            <div class="glass-card-static p-4 mb-4">
                <label class="block text-sm font-medium mb-1" style="color:var(--text-primary);">Message <span class="text-red-500">*</span></label>
                <p class="text-xs mb-2" style="color:var(--text-muted);">Placeholders: &#123;&#123;patient_name&#125;&#125; &#123;&#123;doctor_name&#125;&#125; &#123;&#123;followup_date&#125;&#125; &#123;&#123;followup_time&#125;&#125;</p>
                <textarea name="message" id="sms-message" rows="5" required
                    class="w-full glass-input-light border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                <div class="flex justify-between mt-1">
                    @error('message')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                    <span id="char-count" class="text-xs" style="color:var(--text-muted);">0 / 160</span>
                </div>
            </div>

            {{-- Patient Selection --}}
            <div class="glass-card-static p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium" style="color:var(--text-primary);">Select Patients <span class="text-red-500">*</span></label>
                    <label class="text-xs cursor-pointer font-medium" style="color:var(--text-muted);">
                        <input type="checkbox" id="select-all" onchange="toggleAll(this)" class="mr-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> Select All
                    </label>
                </div>
                @error('patient_ids')<p class="text-red-500 text-xs mb-2">{{ $message }}</p>@enderror
                <div class="max-h-64 overflow-y-auto space-y-1">
                    @forelse($patients as $patient)
                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-indigo-50/50 cursor-pointer transition-colors">
                            <input type="checkbox" name="patient_ids[]" value="{{ $patient->id }}"
                                class="patient-cb rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                data-phone="{{ $patient->phone ?? '' }}">
                            <div class="flex-1">
                                <div class="text-sm font-medium" style="color:var(--text-primary);">{{ $patient->name }}</div>
                                <div class="text-xs" style="color:var(--text-muted);">{{ $patient->phone ?? 'No phone' }}</div>
                            </div>
                        </label>
                    @empty
                        <p class="text-sm py-4 text-center" style="color:var(--text-muted);">No patients found.</p>
                    @endforelse
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3">
                <button type="submit" class="btn-gradient" id="send-btn">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send SMS
                </button>
                <a href="{{ route('doctor.sms-center.index') }}" class="btn-outline-glass px-4 py-2">Cancel</a>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script>
    function loadTemplate(id) {
        if (!id) return;
        const select = document.getElementById('template-select');
        const option = select.querySelector(`option[value="${id}"]`);
        if (option) {
            document.getElementById('sms-message').value = option.dataset.message || '';
            updateCharCount();
        }
    }

    function toggleAll(checkbox) {
        document.querySelectorAll('.patient-cb').forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    function updateCharCount() {
        const len = document.getElementById('sms-message').value.length;
        document.getElementById('char-count').textContent = len + ' / 160';
    }

    document.getElementById('sms-message')?.addEventListener('input', updateCharCount);
    updateCharCount();

    document.getElementById('send-sms-form')?.addEventListener('submit', function(e) {
        const checked = document.querySelectorAll('.patient-cb:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Please select at least one patient.');
        }
    });
</script>
@endpush
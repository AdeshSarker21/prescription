@extends('admin.layouts.app')

@section('title', 'Edit Subscription')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-white/90">Edit Subscription</h1>
            <p class="mt-1 text-sm text-white/50">{{ $subscription->user->name }} — {{ $subscription->plan->name }}</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($subscription->payment_method)
        <div class="glass-card-static p-6 mb-6 space-y-3">
            <h3 class="font-semibold text-white/90">Payment Details</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-white/50">Method</dt>
                    <dd class="font-medium text-white/90">{{ strtoupper($subscription->payment_method) }}</dd>
                </div>
                <div>
                    <dt class="text-white/50">Transaction ID</dt>
                    <dd class="font-medium text-white/90">{{ $subscription->transaction_id }}</dd>
                </div>
                <div>
                    <dt class="text-white/50">Sender Number</dt>
                    <dd class="font-medium text-white/90">{{ $subscription->sender_number }}</dd>
                </div>
                <div>
                    <dt class="text-white/50">Amount Paid</dt>
                    <dd class="font-medium text-white/90">{{ config('app.currency', '$') }}{{ number_format($subscription->amount_paid ?? 0, 2) }}</dd>
                </div>
                @if($subscription->payment_note)
                <div class="col-span-2">
                    <dt class="text-white/50">Note</dt>
                    <dd class="font-medium text-white/90">{{ $subscription->payment_note }}</dd>
                </div>
                @endif
                @if($subscription->approvedBy)
                <div class="col-span-2">
                    <dt class="text-white/50">Approved By</dt>
                    <dd class="font-medium text-emerald-400">{{ $subscription->approvedBy->name }} on {{ $subscription->approved_at?->format('M d, Y h:i A') }}</dd>
                </div>
                @endif
                @if($subscription->renewedFromSubscription)
                <div class="col-span-2">
                    <dt class="text-white/50">Renewed From</dt>
                    <dd class="font-medium text-blue-400">{{ $subscription->renewedFromSubscription->plan->name ?? 'N/A' }} ({{ $subscription->renewedFromSubscription->getBillingCycleLabel() }})</dd>
                </div>
                @endif
            </dl>
        </div>
        @endif

        <form action="{{ route('admin.subscriptions.update', $subscription) }}" method="POST" class="glass-card-static p-6 space-y-6">
            @csrf
            @method('PATCH')

            {{-- Billing Cycle --}}
            <div>
                <label class="block text-sm font-medium text-white/70 mb-2">Billing Cycle</label>
                <div class="flex flex-wrap gap-2" id="billingToggle">
                    @foreach(\App\Models\Plan::billingCycles() as $key => $label)
                    <button type="button" data-cycle="{{ $key }}" class="cycle-btn px-4 py-2 text-sm font-medium rounded-full transition-all {{ $subscription->billing_cycle === $key ? 'btn-gradient' : 'bg-white/10 text-white/60' }}">{{ $label }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="billing_cycle" id="billing_cycle" value="{{ $subscription->billing_cycle ?? 'monthly' }}">
                @error('billing_cycle') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Plan --}}
            <div>
                <label for="plan_id" class="block text-sm font-medium text-white/70">Plan</label>
                <select name="plan_id" id="plan_id" class="mt-1 block w-full glass-input">
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}"
                            data-monthly="{{ $plan->monthly_price }}"
                            data-quarterly="{{ $plan->quarterly_price }}"
                            data-semi_annual="{{ $plan->semi_annual_price }}"
                            data-yearly="{{ $plan->yearly_price }}"
                            data-lifetime="{{ $plan->lifetime_price }}"
                            {{ $subscription->plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} — {{ config('app.currency') }}{{ $plan->monthly_price }}/mo
                        </option>
                    @endforeach
                </select>
                @error('plan_id') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-sm font-medium text-white/70">Status</label>
                <select name="status" id="status" class="mt-1 block w-full glass-input">
                    @foreach (['active', 'expired', 'cancelled', 'pending'] as $s)
                        <option value="{{ $s }}" {{ $subscription->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                @error('status') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Dates (auto generated) --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-white/70">Start Date <span class="text-white/40 font-normal">(auto)</span></label>
                    <input type="datetime-local" name="starts_at" id="starts_at" value="{{ $subscription->starts_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i') }}" class="mt-1 block w-full glass-input">
                    @error('starts_at') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ends_at" class="block text-sm font-medium text-white/70">End Date <span class="text-white/40 font-normal">(auto)</span></label>
                    <input type="datetime-local" name="ends_at" id="ends_at" value="{{ $subscription->ends_at?->format('Y-m-d\TH:i') }}" readonly class="mt-1 block w-full glass-input cursor-not-allowed opacity-60">
                    @error('ends_at') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-gradient">Update</button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn-outline-glass">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
const cycleButtons = document.querySelectorAll('.cycle-btn');
const cycleInput = document.getElementById('billing_cycle');
const planSelect = document.getElementById('plan_id');
const startInput = document.getElementById('starts_at');
const endInput = document.getElementById('ends_at');

const cycleDays = {
    monthly: 30,
    quarterly: 90,
    semi_annual: 180,
    yearly: 365,
    lifetime: 36500
};

const cycleLabels = {
    monthly: 'mo',
    quarterly: '3mo',
    semi_annual: '6mo',
    yearly: 'yr',
    lifetime: 'life'
};

function setCycle(cycle) {
    cycleInput.value = cycle;
    cycleButtons.forEach(btn => {
        const active = btn.dataset.cycle === cycle;
        btn.classList.toggle('btn-gradient', active);
        btn.classList.toggle('bg-white/10', !active);
        btn.classList.toggle('text-white/60', !active);
    });
    updatePlanPrices(cycle);
    calcEndDate();
}

function updatePlanPrices(cycle) {
    const label = cycleLabels[cycle] || 'mo';
    Array.from(planSelect.options).forEach(opt => {
        if (opt.value && !opt.disabled) {
            const price = opt.dataset[cycle] || 0;
            const name = opt.text.split(' — ')[0];
            opt.text = name + ' — ' + window.AppCurrency + price + '/' + label;
        }
    });
}

function calcEndDate() {
    const val = startInput.value;
    if (!val) return;
    const start = new Date(val);
    const days = cycleDays[cycleInput.value] || 30;
    start.setDate(start.getDate() + days);
    const pad = n => String(n).padStart(2, '0');
    endInput.value = start.getFullYear() + '-' + pad(start.getMonth() + 1) + '-' + pad(start.getDate()) + 'T' + pad(start.getHours()) + ':' + pad(start.getMinutes());
}

cycleButtons.forEach(btn => btn.addEventListener('click', () => setCycle(btn.dataset.cycle)));
startInput.addEventListener('change', calcEndDate);

window.AppCurrency = '{{ config('app.currency', '$') }}';
setCycle(cycleInput.value);
</script>
@endpush

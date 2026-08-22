@extends('admin.layouts.app')

@section('title', 'Payment Settings')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-white/90">Payment Settings</h1>
            <p class="mt-1 text-sm text-white/50">Manage payment methods (bKash, Nagad, etc.)</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Add New --}}
        <div class="glass-card-static p-6">
            <h3 class="text-lg font-semibold text-white/90 mb-4">Add Payment Method</h3>
            <form action="{{ route('admin.settings.payment.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Method Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. bKash" required class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Type</label>
                    <select name="type" required class="w-full glass-input">
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                        <option value="bank">Bank</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" placeholder="e.g. 01XXXXXXXXX" required class="w-full glass-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/70 mb-1">Account Holder (optional)</label>
                    <input type="text" name="account_holder" value="{{ old('account_holder') }}" placeholder="e.g. John Doe" class="w-full glass-input">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-white/70 mb-1">Instructions (optional)</label>
                    <textarea name="instructions" rows="2" class="w-full glass-input" placeholder="Payment instructions for doctors...">{{ old('instructions') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="btn-gradient">Add Method</button>
                </div>
            </form>
        </div>

        {{-- Existing Methods --}}
        <div class="glass-card-static overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h3 class="text-lg font-semibold text-white/90">Saved Payment Methods</h3>
            </div>
            @if($methods->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($methods as $method)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-white/90">{{ $method->name }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $method->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-white/10 text-white/50' }}">
                                {{ $method->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-sm text-white/60 mt-0.5">{{ $method->type }} — {{ $method->account_number }}</p>
                        @if($method->account_holder)
                        <p class="text-xs text-white/50">{{ $method->account_holder }}</p>
                        @endif
                        @if($method->instructions)
                        <p class="text-xs text-white/40 mt-1">{{ Str::limit($method->instructions, 100) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <form action="{{ route('admin.settings.payment.update', $method) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="name" value="{{ $method->name }}">
                            <input type="hidden" name="type" value="{{ $method->type }}">
                            <input type="hidden" name="account_number" value="{{ $method->account_number }}">
                            <input type="hidden" name="account_holder" value="{{ $method->account_holder }}">
                            <input type="hidden" name="instructions" value="{{ $method->instructions }}">
                            <input type="hidden" name="is_active" value="{{ $method->is_active ? 0 : 1 }}">
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ $method->is_active ? 'bg-white/10 text-white/60 hover:bg-white/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }}">
                                {{ $method->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.settings.payment.destroy', $method) }}" method="POST" onsubmit="return confirm('Remove this payment method?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20">Remove</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-6 py-12 text-center text-sm text-white/50">No payment methods configured yet.</div>
            @endif
        </div>
    </div>
@endsection

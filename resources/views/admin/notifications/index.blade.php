@php
    $typeIcons = [
        'subscription_payment' => 'cash',
        'medicine_suggestion' => 'capsule',
        'doctor_registration' => 'user-plus',
    ];
    $typeColors = [
        'subscription_payment' => 'green',
        'medicine_suggestion' => 'blue',
        'doctor_registration' => 'indigo',
    ];
@endphp

@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-white/90">Notifications</h1>
            <p class="mt-1 text-sm text-white/50">All system notifications.</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="glass-card-static overflow-hidden">
            @if($notifications->count() > 0)
            <div class="divide-y divide-white/5">
                @foreach($notifications as $n)
                <div class="flex items-start gap-4 px-6 py-4 {{ is_null($n->read_at) ? 'bg-indigo-500/10' : '' }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-white/90">{{ $n->data['message'] ?? 'Notification' }}</p>
                        <p class="text-xs text-white/40 mt-1">{{ $n->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @if(is_null($n->read_at))
                    <form action="{{ route('admin.notifications.mark-read', $n->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium whitespace-nowrap">Mark read</button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="px-6 py-4 border-t border-white/5">
                {{ $notifications->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <svg class="w-12 h-12 text-white/20 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm text-white/50">No notifications yet.</p>
            </div>
            @endif
        </div>
    </div>
@endsection

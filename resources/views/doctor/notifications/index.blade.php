@extends('doctor.layouts.app')

@section('title', 'Notifications')

@section('header', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($notifications->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($notifications as $n)
            <div class="flex items-start gap-4 px-6 py-4 {{ is_null($n->read_at) ? 'bg-indigo-50/30' : '' }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900">{{ $n->data['message'] ?? 'Notification' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->format('M d, Y h:i A') }}</p>
                </div>
                @if(is_null($n->read_at))
                <form action="{{ route('doctor.notifications.mark-read', $n->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap">Mark read</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $notifications->links() }}
        </div>
        @else
        <div class="text-center py-16">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-sm text-gray-500">No notifications yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection

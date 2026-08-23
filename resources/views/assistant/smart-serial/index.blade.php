@extends('assistant.layouts.app')

@section('title', 'Smart Serial Queue')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Smart Serial Queue</h1>
            <p class="text-gray-500 mt-1">Manage patient queue</p>
        </div>
        @if($session && $session->status === 'active')
            <div class="flex gap-2">
                <form method="POST" action="{{ route('assistant.smart-serial.pause', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">Pause</button>
                </form>
                <form method="POST" action="{{ route('assistant.smart-serial.close', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Close</button>
                </form>
            </div>
        @elseif($session && $session->status === 'paused')
            <form method="POST" action="{{ route('assistant.smart-serial.resume', $session->id) }}">
                @csrf @method('PATCH')
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Resume</button>
            </form>
        @else
            <form method="POST" action="{{ route('assistant.smart-serial.start') }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Start Session</button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

    @if($session)
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-4 text-center"><div class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</div><div class="text-sm text-gray-500">Total</div></div>
            <div class="bg-white rounded-xl shadow p-4 text-center"><div class="text-3xl font-bold text-yellow-500">{{ $stats['waiting'] }}</div><div class="text-sm text-gray-500">Waiting</div></div>
            <div class="bg-white rounded-xl shadow p-4 text-center"><div class="text-3xl font-bold text-orange-500">{{ $stats['called'] + $stats['in_consultation'] }}</div><div class="text-sm text-gray-500">In Progress</div></div>
            <div class="bg-white rounded-xl shadow p-4 text-center"><div class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</div><div class="text-sm text-gray-500">Completed</div></div>
        </div>

        @if(in_array('create_serial', $permissions))
        <div class="bg-white rounded-xl shadow p-4">
            <h3 class="font-semibold mb-3">Add Patient to Queue</h3>
            <form method="POST" action="{{ route('assistant.smart-serial.add-patient') }}" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Patient ID</label>
                    <input type="number" name="patient_id" required class="mt-1 block w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                    <select name="priority" class="mt-1 border rounded-lg px-3 py-2">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add</button>
            </form>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow">
            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="font-semibold">Queue ({{ $queue->count() }} patients)</h3>
                @if(in_array('call_next', $permissions) && $session->status === 'active')
                <form method="POST" action="{{ route('assistant.smart-serial.call-next', $session->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Call Next</button>
                </form>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Patient</th>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($queue as $item)
                        <tr class="{{ $item->status === 'called' ? 'bg-yellow-50' : ($item->status === 'in_consultation' ? 'bg-blue-50' : '') }}">
                            <td class="px-4 py-3 font-bold text-lg">{{ $item->serial_number }}</td>
                            <td class="px-4 py-3">{{ $item->patient->name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">
                                @if($item->priority === 'emergency')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">EMERGENCY</span>
                                @elseif($item->priority === 'urgent')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">Urgent</span>
                                @elseif($item->priority === 'vip')
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold">VIP</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status === 'waiting')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Waiting</span>
                                @elseif($item->status === 'called')
                                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">Called</span>
                                @elseif($item->status === 'in_consultation')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">In Consultation</span>
                                @elseif($item->status === 'completed')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">Completed</span>
                                @elseif($item->status === 'cancelled')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1 flex-wrap">
                                    @if($item->status === 'waiting' && in_array('call_next', $permissions))
                                        <form method="POST" action="{{ route('assistant.smart-serial.call-patient', $item->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-blue-500 text-white rounded text-xs">Call</button>
                                        </form>
                                    @endif
                                    @if($item->status === 'called' && in_array('call_next', $permissions))
                                        <form method="POST" action="{{ route('assistant.smart-serial.start-consultation', $item->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-indigo-500 text-white rounded text-xs">Start</button>
                                        </form>
                                    @endif
                                    @if($item->status === 'in_consultation' && in_array('complete', $permissions))
                                        <form method="POST" action="{{ route('assistant.smart-serial.complete', $item->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-green-500 text-white rounded text-xs">Done</button>
                                        </form>
                                    @endif
                                    @if(in_array('cancel_serial', $permissions) && !in_array($item->status, ['completed','cancelled']))
                                        <form method="POST" action="{{ route('assistant.smart-serial.cancel', $item->id) }}">
                                            @csrf @method('DELETE')
                                            <button class="px-2 py-1 bg-red-500 text-white rounded text-xs">X</button>
                                        </form>
                                    @endif
                                    @if(in_array('emergency', $permissions) && $item->priority !== 'emergency' && !in_array($item->status, ['completed','cancelled']))
                                        <form method="POST" action="{{ route('assistant.smart-serial.emergency', $item->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-red-700 text-white rounded text-xs">!</button>
                                        </form>
                                    @endif
                                    @if(in_array('recall', $permissions) && in_array($item->status, ['called','completed']))
                                        <form method="POST" action="{{ route('assistant.smart-serial.recall', $item->id) }}">
                                            @csrf @method('PATCH')
                                            <button class="px-2 py-1 bg-yellow-500 text-white rounded text-xs">Recall</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No patients in queue</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl shadow p-12 text-center text-gray-400">
            <p class="text-lg">No active session. Click "Start Session" to begin.</p>
        </div>
    @endif
</div>
@endsection

@php
    $nameField = $config['nameField'];
    $detailsField = $config['detailsField'] ?? null;
    $itemName = $item->$nameField ?? '';
    $itemDetails = $detailsField ? ($item->$detailsField ?? '') : '';
@endphp
@if($items->isEmpty())
    <tr><td colspan="{{ $detailsField ? 6 : 5 }}" class="text-center py-8 text-white/40">No records found.</td></tr>
@else
    @foreach($items as $index => $item)
        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
            <td class="px-4 py-3 text-white/40">{{ $items->firstItem() + $index }}</td>
            <td class="px-4 py-3 text-white/90 font-medium">{{ $item->$nameField }}</td>
            @if($detailsField)
            <td class="px-4 py-3 text-white/60 text-sm max-w-xs truncate">{{ $item->$detailsField ?? '—' }}</td>
            @endif
            <td class="px-4 py-3">
                <form method="POST" action="{{ route('admin.master-data.toggle-status', [$module, $item->id]) }}" class="inline">
                    @csrf
                    @if($item->status === 'active')
                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors cursor-pointer">
                            Active
                        </button>
                    @else
                        <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-colors cursor-pointer">
                            Inactive
                        </button>
                    @endif
                </form>
            </td>
            <td class="px-4 py-3 text-white/50">{{ $item->used_count ?? 0 }}</td>
            <td class="px-4 py-3 text-right space-x-2">
                <a href="{{ route('admin.master-data.edit', [$module, $item->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg hover:bg-indigo-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.master-data.destroy', [$module, $item->id]) }}" class="inline" data-confirm="Delete {{ $item->$nameField }}? This cannot be undone." data-title="Delete Record" data-icon="warning">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg hover:bg-red-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
@endif

@extends('statamic::layout')

@section('title', 'Notion Database: ' . ($mapping->name ?? $databaseId))

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="flex-1">{{ $mapping->name ?? 'Notion Database' }}</h1>
    </div>

    <div class="card p-0">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="w-8"></th>
                    <th>Title</th>
                    <th class="actions-column"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr id="row-{{ $row['id'] }}">
                        <td class="text-center">
                            @if(in_array($row['id'], $mapping->mapped_pages ?? []))
                                <span class="text-green-600 text-lg font-bold">✓</span>
                            @else
                                <span class="text-gray-400 text-lg">○</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center">
                                <span>{{ $row['properties']['Name']['title'][0]['plain_text'] ?? $row['properties']['Title']['title'][0]['plain_text'] ?? 'Untitled' }}</span>
                            </div>
                        </td>
                        <td class="flex justify-end">
                            @if(in_array($row['id'], $mapping->mapped_pages ?? []))
                                <div class="flex items-center">
                                    <a href="{{ cp_route('collections.entries.index', ['collection' => $mapping->collection_handle]) }}" 
                                       class="btn-primary" 
                                       target="_blank">
                                        View Entry
                                    </a>
                                </div>
                            @else
                                <button class="btn-primary"
                                        data-page-id="{{ $row['id'] }}"
                                        onclick="importEntry('{{ $row['id'] }}')"
                                >
                                    Import Entry
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center p-6 text-gray-500">
                            No entries found in this database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Loading overlay --}}
    <div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Importing entry...</span>
        </div>
    </div>

    <script>
        function importEntry(pageId) {
            const row = document.getElementById(`row-${pageId}`);
            const button = row.querySelector('button');
            const statusCell = row.querySelector('td:first-child');
            const loadingOverlay = document.getElementById('loading-overlay');
            
            button.disabled = true;
            loadingOverlay.classList.remove('hidden');
        
            fetch('{{ cp_route("notion-mapping.import-entry", ["databaseId" => $databaseId]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ page_id: pageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status icon using Statamic's icon
                    statusCell.innerHTML = `@cp_svg('icons/light/check-circle', 'h-6 w-6 text-green-600')`;
                    
                    // Update action button
                    const actionsCell = row.querySelector('td:last-child');
                    actionsCell.innerHTML = `
                        <div class="flex items-center">
                            <a href="{{ cp_route('collections.entries.index', ['collection' => $mapping->collection_handle]) }}" 
                               class="btn-primary" 
                               target="_blank">
                                View Entry
                            </a>
                        </div>
                    `;
        
                    Statamic.$toast.success('Entry imported successfully');
                } else {
                    throw new Error(data.message || 'Import failed');
                }
            })
            .catch(error => {
                Statamic.$toast.error(error.message);
                button.disabled = false;
            })
            .finally(() => {
                loadingOverlay.classList.add('hidden');
            });
        }
        </script>
@endsection

@push('scripts')

@endpush

@push('styles')
<style>
.actions-column {
    width: 180px;
}
</style>
@endpush 
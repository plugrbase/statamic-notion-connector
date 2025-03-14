@extends('statamic::layout')

@section('title', 'Notion Connector')

@section('content')
    <header class="mb-6">
        {{-- Breadcrumb --}}
        <div class="flex items-center justify-between">
            <h1>{{ __('Notion Connector') }}</h1>
        </div>
    </header>

    {{-- Content Card --}}
    <div class="card p-0">
        <div class="p-4">
            <h2 class="text-lg font-bold mb-3">Database Mappings</h2>
            <p class="text-gray-700 text-sm mb-4">
                Connect your Notion databases to Statamic collections.
            </p>
        </div>

        {{-- Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th>Database Name</th>
                    <th>Collection</th>
                    <th>Entries</th>
                    <th class="actions-column"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($mappings as $mapping)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <span class="font-medium">{{ $mapping->name }}</span>
                                    <span class="text-gray-500 text-xs block">{{ $mapping->database_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-sm">{{ $mapping->collection_handle }}</span>
                        </td>
                        <td>
                            <span class="text-sm">
                                {{ count($mapping->mapped_pages ?? []) }} mapped
                            </span>
                        </td>
                        <td class="flex items-center justify-end">
                            <div class="btn-group">
                                <a href="{{ cp_route('notion-mapping.view', $mapping->id) }}" 
                                   class="btn-primary mr-2"
                                >
                                    View Entries
                                </a>
                                <a href="{{ cp_route('notion-mapping.edit', $mapping->id) }}" 
                                   class="btn"
                                >
                                    Edit Mapping
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center p-6">
                            <div class="max-w-md mx-auto">
                                <div class="flex flex-col items-center">
                                    {{-- Empty state icon --}}
                                    <div class="mb-4">
                                        <svg width="48" height="48" viewBox="0 0 24 24" class="text-gray-400">
                                            <path fill="currentColor" d="M20 6v12H4V6h16m0-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="mb-2 text-gray-900 font-bold">No Database Mappings</h3>
                                    <p class="text-gray-500 text-sm text-center mb-4">
                                        Get started by connecting a Notion database to your Statamic collections.
                                    </p>
                                    <a href="{{ cp_route('notion-mapping.create') }}" class="btn-primary">
                                        Create First Mapping
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Action Button --}}
    @if(count($mappings) > 0)
        <div class="flex justify-end mt-6">
            <a href="{{ cp_route('notion-mapping.create') }}" class="btn-primary">
                Add Database Mapping
            </a>
        </div>
    @endif

    {{-- Help Card --}}
    <div class="card mt-6 p-4">
        <div class="flex items-start">
            <div class="w-6 h-6 text-gray-500 mr-2 flex-shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold mb-2">About Notion Connector</h3>
                <p class="text-gray-700 text-sm">
                    This addon allows you to sync content from your Notion databases directly into Statamic collections. 
                    Create a mapping to specify how Notion fields should map to your Statamic fields.
                </p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.actions-column {
    width: 220px;
}
</style>
@endpush 
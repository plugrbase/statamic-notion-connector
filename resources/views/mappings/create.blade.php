@extends('statamic::layout')

@section('title', 'Create Database Mapping')

@section('content')
    <header class="mb-6">
        <div class="flex items-center justify-between">
            <h1>Create Database Mapping</h1>
        </div>
    </header>

    <div class="card p-0">
        <form method="POST" action="{{ cp_route('notion-mapping.store') }}" class="p-4">
            @csrf

            <div class="mb-6">
                <label class="font-bold text-base mb-2 block" for="name">
                    Database Name
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="input-text" 
                       value="{{ old('name') }}" 
                       required>
                @error('name')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-6">
                <label class="font-bold text-base mb-2 block" for="database_id">
                    Notion Database
                </label>
                <div class="select-input-container">
                    <select name="database_id" 
                            id="database_id" 
                            class="select-input" 
                            required>
                        <option value="">Select a database...</option>
                        @foreach($databases as $database)
                            <option value="{{ $database['id'] }}" 
                                    {{ old('database_id') == $database['id'] ? 'selected' : '' }}
                                    data-icon="database">
                                {{ $database['title'] ?? $database['id'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="select-input-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                     </div>
                </div>
                @error('database_id')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
                <p class="text-gray-500 text-xs mt-1">
                    Select the Notion database you want to connect with your Statamic collection.
                </p>
                @if(empty($databases))
                    <div class="text-sm text-red-500 mt-2">
                        No databases found. Please check your Notion connection and permissions.
                    </div>
                @endif
            </div>

            <div class="mb-6">
                <label class="font-bold text-base mb-2 block" for="collection_handle">
                    Statamic Collection
                </label>
                <div class="select-input-container">
                    <select name="collection_handle" 
                        id="collection_handle" 
                        class="select-input" 
                        required>
                    <option value="">Select a collection...</option>
                    @foreach($collections as $collection)
                        <option value="{{ $collection->handle() }}" 
                                {{ old('collection_handle') == $collection->handle() ? 'selected' : '' }}>
                            {{ $collection->title() }}
                        </option>
                    @endforeach
                    </select>
                    <div class="select-input-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                     </div>
                </div>
                @error('collection_handle')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
                <p class="text-gray-500 text-xs mt-1">
                    Select the Statamic collection you want to connect with your Notion database.
                </p>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="btn-primary">
                    Continue
                </button>

                <a href="{{ cp_route('notion-mapping.index') }}" class="btn">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const databaseSelect = document.getElementById('database_id');
    
    databaseSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const databaseId = this.value;
        
        if (!databaseId) return;

        // You could add additional logic here, like fetching database fields
        // or updating other parts of the form based on the selected database
    });
});
</script>
@endpush

@push('styles')
<style>
.btn-close {
    @apply p-1 text-gray-500 hover:text-gray-700 text-lg font-bold;
}
</style>
@endpush 
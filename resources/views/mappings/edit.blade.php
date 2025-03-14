@extends('statamic::layout')

@section('title', 'Edit Database Mapping')

@section('content')
    <header class="mb-6">
        <div class="flex items-center justify-between">
            <h1>Edit Database Mapping</h1>
        </div>
    </header>

    <div class="card p-0">
        <form method="POST" action="{{ cp_route('notion-mapping.update', $mapping->id) }}" class="p-4">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="font-bold text-base mb-2 block" for="name">
                    Database Name
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       class="input-text" 
                       value="{{ old('name', $mapping->name) }}" 
                       required>
                @error('name')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-6">
                <label class="font-bold text-base mb-2 block" for="database_id">
                    Database ID
                </label>
                <input type="text" 
                       class="input-text bg-gray-100" 
                       value="{{ $mapping->database_id }}" 
                       readonly 
                       disabled>
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
                                    {{ old('collection_handle', $mapping->collection_handle) == $collection->handle() ? 'selected' : '' }}>
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
            </div>

            <div class="mb-6">
                <label class="font-bold text-base mb-2 block">
                    Field Mappings
                </label>
                <div class="card p-4 bg-gray-50">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left pb-2">Notion Field</th>
                                <th class="text-left pb-2">Statamic Field</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fields as $notionField => $value)
                                <tr class="border-t border-gray-200">
                                    <td class="py-2">
                                        <div class="flex items-center">
                                            <span class="font-medium">{{ $notionField }}</span>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <div class="select-input-container">
                                            <select name="field_mappings[{{ $notionField }}]" 
                                                    class="select-input w-full">
                                                <option value="">Select Statamic field...</option>
                                                @foreach($collections->first()->entryBlueprint()->fields()->all() as $handle => $field)
                                                    <option value="{{ $handle }}"
                                                            {{ old("field_mappings.$notionField", $mapping->field_mappings[$notionField] ?? '') == $handle ? 'selected' : '' }}>
                                                        {{ $field->display() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="select-input-toggle">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                                </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('field_mappings')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="btn-primary">
                    Save Mapping
                </button>

                <a href="{{ cp_route('notion-mapping.index') }}" class="btn">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Danger Zone --}}
    <div class="card p-0 mt-6">
        <div class="p-4">
            <h2 class="text-lg font-bold mb-3 text-red-500">Danger Zone</h2>
            <form method="POST" 
                  action="{{ cp_route('notion-mapping.destroy', $mapping->id) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this mapping?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    Delete Mapping
                </button>
            </form>
        </div>
    </div>
@endsection 
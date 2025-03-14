<?php

namespace Plugrbase\StatamicNotionConnector\Http\Controllers;

use Illuminate\Http\Request;
use Plugrbase\StatamicNotionConnector\Notion\NotionClient;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Plugrbase\StatamicNotionConnector\Models\NotionMapping;
use Exception;

class NotionMappingController extends CpController
{
    protected NotionClient $notionClient;

    public function __construct(NotionClient $notionClient)
    {
        $this->notionClient = $notionClient;
    }

    /**
     * Display a listing of the mappings.
     */
    public function index()
    {
        $mappings = NotionMapping::all();
        return view('statamic-notion-connector::index', compact('mappings'));
    }

    /**
     * Show the form for creating a new mapping.
     */
    public function create()
    {
        try {
            $databases = $this->notionClient->getDatabases();

            return view('statamic-notion-connector::mappings.create', [
                'collections' => Collection::all(),
                'databases' => $databases ?? []
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Failed to fetch Notion databases: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created mapping in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'database_id' => 'required|string',
            'collection_handle' => 'required|string'
        ]);

        try {
            $mapping = NotionMapping::create($validated);

            return redirect()
                ->route('statamic.cp.notion-mapping.edit', $mapping->id)
                ->with('success', 'Database mapping created successfully.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create mapping: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified mapping.
     */
    public function show(NotionMapping $mapping)
    {
        try {
            $database = $this->notionClient->getDatabaseContent($mapping->database_id);

            $rows = $database['results'] ?? [];

            ray($rows);

            return view('statamic-notion-connector::mappings.show', [
                'mapping' => $mapping,
                'database' => $database,
                'databaseId' => $mapping->database_id,
                'rows' => $rows ?? []
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Failed to fetch database content: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified mapping.
     */
    public function edit(NotionMapping $mapping)
    {
        $databases = $this->notionClient->getDatabases();

        $content = $this->notionClient->getDatabaseContent($mapping->database_id);
        $rows = $content['results'] ?? [];

        // Format the rows for display
        $fields = collect($rows)->map(function ($row) use ($mapping) {
            $formattedRow = [
                'id' => $row['id'],
                'properties' => []
            ];

            foreach ($row['properties'] as $key => $property) {
                $value = $this->transformProperty($property);
                $formattedRow['properties'][$key] = $value;
            }

            return $formattedRow;
        });

        return view('statamic-notion-connector::mappings.edit', [
            'mapping' => $mapping,
            'collection' => Collection::findByHandle($mapping->collection_handle)?->entryBlueprint()->fields()->all() ?? [],
            'collections' => Collection::all(),
            'fields' => $fields[0]['properties'] ?? [],
            'databases' => $databases ?? []
        ]);
    }

    /**
     * Update the specified mapping in storage.
     */
    public function update(Request $request, NotionMapping $mapping)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'collection_handle' => 'required|string',
            'field_mappings' => 'required|array'
        ]);

        try {
            $mapping->update($validated);

            return redirect()
                ->route('statamic.cp.notion-mapping.index')
                ->with('success', 'Database mapping updated successfully.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update mapping: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified mapping from storage.
     */
    public function destroy(NotionMapping $mapping)
    {
        try {
            $mapping->delete();

            return redirect()
                ->route('statamic.cp.notion-mapping.index')
                ->with('success', 'Database mapping deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete mapping: ' . $e->getMessage());
        }
    }

    /**
     * View database entries for a specific mapping.
     */
    public function viewDatabase($databaseId)
    {
        try {
            $mapping = NotionMapping::where('database_id', $databaseId)->firstOrFail();
            $database = $this->notionClient->queryDatabase($databaseId);

            return view('statamic-notion-connector::database', [
                'mapping' => $mapping,
                'databaseId' => $databaseId,
                'rows' => $database['results'] ?? []
            ]);
        } catch (Exception $e) {
            return back()->with('error', 'Failed to fetch database content: ' . $e->getMessage());
        }
    }

    /**
     * Import a single entry from Notion.
     */
    public function importEntry(Request $request, $databaseId)
    {
        try {
            $pageId = $request->input('page_id');
            $mapping = NotionMapping::where('database_id', $databaseId)->firstOrFail();

            // Get the page data from Notion
            $pageData = $this->notionClient->getPage($pageId);

            // Transform Notion data to Statamic format
            $statamicData = [];
            foreach ($mapping->field_mappings as $notionField => $statamicField) {
                if (empty($statamicField)) continue;
                
                $property = $pageData['properties'][$notionField] ?? null;
                if ($property) {
                    $statamicData[$statamicField] = $this->transformProperty($property);
                }
            }

            // Create Statamic entry
            $entry = Entry::make()
                ->collection($mapping->collection_handle)
                ->slug($this->generateSlug($pageData, $mapping))
                ->data($statamicData);

            $entry->save();

            // Mark page as mapped
            $mappedPages = $mapping->mapped_pages ?? [];
            $mappedPages[] = $pageId;
            $mapping->mapped_pages = array_unique($mappedPages);
            $mapping->save();

            return response()->json([
                'success' => true,
                'message' => 'Entry imported successfully',
                'entry_id' => $entry->id()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function transformProperty($property)
    {
        switch ($property['type']) {
            case 'title':
                return $property['title'][0]['plain_text'] ?? '';
            
            case 'rich_text':
                return collect($property['rich_text'])->pluck('plain_text')->join(' ');
            
            case 'select':
                return $property['select']['name'] ?? null;
            
            case 'multi_select':
                return collect($property['multi_select'])->pluck('name')->toArray();
            
            case 'date':
                if (!isset($property['date'])) return null;
                if (isset($property['date']['end'])) {
                    return [
                        'start' => $property['date']['start'],
                        'end' => $property['date']['end']
                    ];
                }
                return $property['date']['start'];
            
            case 'checkbox':
                return $property['checkbox'] ?? false;
            
            case 'number':
                return $property['number'] ?? null;
            
            case 'url':
                return $property['url'] ?? '';
            
            case 'email':
                return $property['email'] ?? '';
            
            case 'phone_number':
                return $property['phone_number'] ?? '';
            
            case 'files':
                return collect($property['files'])->pluck('file.url')->toArray();
            
            case 'relation':
                return collect($property['relation'])->pluck('id')->toArray();

            default:
                return null;
        }
    }

    protected function generateSlug($pageData, $mapping)
    {
        // Try to get title from mapped fields
        $titleField = array_search('title', $mapping->field_mappings) ?? 'Title';
        
        // Get the title value from Notion page data
        $title = $pageData['properties'][$titleField]['title'][0]['plain_text'] ?? null;
        
        if ($title) {
            return str()->slug($title);
        }
        
        // Fallback to a portion of the Notion page ID
        return 'notion-' . substr($pageData['id'], 0, 8);
    }
}

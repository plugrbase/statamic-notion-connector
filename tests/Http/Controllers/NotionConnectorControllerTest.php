<?php

namespace Plugrbase\StatamicNotionConnector\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Collection;
use Statamic\Facades\Blueprint;
use Plugrbase\StatamicNotionConnector\Tests\TestCase;
use Plugrbase\StatamicNotionConnector\Models\NotionMapping;

class NotionConnectorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test collection
        Collection::make('test_collection')
            ->title('Test Collection')
            ->save();
    }

    /** @test */
    public function it_can_fetch_collections()
    {
        $response = $this->get(cp_route('notion-connector.collections'));

        $response->assertOk()
            ->assertJsonStructure(['collections' => [['handle', 'title']]])
            ->assertJson([
                'collections' => [
                    ['handle' => 'test_collection', 'title' => 'Test Collection']
                ]
            ]);
    }

    /** @test */
    public function it_can_store_mappings()
    {
        $data = [
            'database_id' => 'test-db-id',
            'collection_handle' => 'test_collection',
            'field_mappings' => ['title' => 'name'],
            'enabled' => true
        ];

        $response = $this->post(cp_route('notion-connector.mappings.store'), $data);

        $response->assertOk();
        $this->assertDatabaseHas('notion_mappings', [
            'database_id' => 'test-db-id',
            'collection_handle' => 'test_collection',
            'enabled' => true
        ]);
    }

    /** @test */
    public function it_validates_mapping_data()
    {
        $response = $this->post(cp_route('notion-connector.mappings.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['database_id', 'collection_handle', 'field_mappings']);
    }
} 
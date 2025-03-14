<?php

namespace Plugrbase\StatamicNotionConnector\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Plugrbase\StatamicNotionConnector\Notion\NotionClient;
use Plugrbase\StatamicNotionConnector\Exceptions\NotionApiException;

class NotionClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['notion-connector.notion.auth_token' => 'test-token']);
    }

    /** @test */
    public function it_can_fetch_databases()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'results' => [
                    [
                        'id' => 'db1',
                        'title' => [['plain_text' => 'Test Database']],
                        'properties' => [
                            'Name' => ['type' => 'title'],
                            'Description' => ['type' => 'rich_text']
                        ]
                    ]
                ]
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $notionClient = new NotionClient();
        $notionClient->setClient($client);

        $databases = $notionClient->getDatabases();

        $this->assertCount(1, $databases);
        $this->assertEquals('db1', $databases->first()['id']);
        $this->assertEquals('Test Database', $databases->first()['title']);
    }

    /** @test */
    public function it_throws_exception_on_api_error()
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode([
                'message' => 'Invalid authentication token'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);
        
        $notionClient = new NotionClient();
        $notionClient->setClient($client);

        $this->expectException(NotionApiException::class);
        $notionClient->getDatabases();
    }
} 
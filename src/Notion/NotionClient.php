<?php

namespace Plugrbase\StatamicNotionConnector\Notion;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Plugrbase\StatamicNotionConnector\Exceptions\NotionApiException;

class NotionClient
{
    protected string $baseUrl = 'https://api.notion.com/v1';
    protected string $token;
    protected array $defaultHeaders;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->defaultHeaders = [
            'Authorization' => "Bearer {$this->token}",
            'Notion-Version' => '2022-06-28',
        ];

        // Test connection using /users endpoint (which we know works)
        $this->testConnection();
    }

    protected function testConnection(): void
    {
        try {
            $response = Http::withHeaders($this->defaultHeaders)
                ->get("{$this->baseUrl}/users/me");

            if (!$response->successful()) {
                throw new \Exception("Failed to connect to Notion API: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Notion test connection failed', [
                'error' => $e->getMessage(),
                'headers' => $this->defaultHeaders,
            ]);
            throw $e;
        }
    }

    public function getDatabases()
    {
        try {
            $response = Http::withHeaders($this->defaultHeaders)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/search", [
                    'filter' => ['property' => 'object', 'value' => 'database']
                ]);

            $results = $response->json()['results'] ?? [];

            // Format the databases to ensure title is a string
            return array_map(function ($database) {
                $title = '';
                if (!empty($database['title'])) {
                    if (is_array($database['title']) && !empty($database['title'][0]['plain_text'])) {
                        $title = $database['title'][0]['plain_text'];
                    } elseif (is_string($database['title'])) {
                        $title = $database['title'];
                    }
                }

                return [
                    'id' => $database['id'],
                    'title' => $title,
                    'properties' => $database['properties'] ?? []
                ];
            }, $results);
        } catch (\Exception $e) {
            Log::error('Failed to fetch Notion databases', [
                'error' => $e->getMessage(),
                'headers' => $this->defaultHeaders,
            ]);
            throw $e;
        }
    }

    public function getDatabase(string $databaseId): array
    {
        try {
            $response = Http::withHeaders($this->defaultHeaders)
                ->get("{$this->baseUrl}/databases/{$databaseId}");

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch database {$databaseId}: " . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to fetch Notion database', [
                'database_id' => $databaseId,
                'error' => $e->getMessage(),
                'headers' => $this->defaultHeaders,
            ]);
            throw $e;
        }
    }

    public function getDatabaseContent(string $databaseId, ?string $startCursor = null): array
    {
        try {
            $payload = [
                'page_size' => Config::get('notion-connector.sync.chunk_size', 100),
            ];

            if ($startCursor) {
                $payload['start_cursor'] = $startCursor;
            }

            $response = Http::withHeaders($this->defaultHeaders)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(
                    "{$this->baseUrl}/databases/{$databaseId}/query",
                    (object)[]  // Cast empty array to object to ensure it's sent as {} not []
                );

            $statusCode = $response->status();
            $body = $response->body();

            if ($statusCode !== 200) {
                throw new NotionApiException("Notion API error: HTTP $statusCode - $body");
            }

            return json_decode($body, true);
        } catch (\Exception $e) {
            Log::error('Failed to fetch database content', [
                'error' => $e->getMessage(),
                'database_id' => $databaseId,
            ]);
            throw new NotionApiException("Failed to fetch database content: {$e->getMessage()}");
        }
    }

    public function getPage(string $pageId): array
    {
        $response = Http::withHeaders($this->defaultHeaders)
            ->get("{$this->baseUrl}/pages/{$pageId}");

        return $response->json();
    }
}

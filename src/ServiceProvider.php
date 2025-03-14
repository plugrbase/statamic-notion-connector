<?php

namespace Plugrbase\StatamicNotionConnector;

use Illuminate\Support\Facades\Config;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;
use Plugrbase\StatamicNotionConnector\Notion\NotionClient;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php'
    ];

    protected $viewNamespace = 'statamic-notion-connector';

    protected $vite = [
        'input' => [
            'resources/js/cp.js'
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-notion-connector');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/notion-connector.php' => config_path('notion-connector.php'),
            ], 'notion-connector-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations')
            ], 'notion-connector-migrations');
        }

        $this->app->booted(function () {
            Nav::extend(function ($nav) {
                $nav->content('Notion Connector')
                    ->section('Tools')
                    ->route('notion-mapping.index')
                    ->icon('git');
            });
        });
    }

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/notion-connector.php', 'notion-connector');

        $this->app->singleton(NotionClient::class, function ($app) {
            $token = Config::get('notion-connector.notion.auth_token');
            
            if (empty($token)) {
                throw new \RuntimeException('Notion API token not configured. Please set NOTION_AUTH_TOKEN in your .env file.');
            }

            return new NotionClient($token);
        });
    }
}

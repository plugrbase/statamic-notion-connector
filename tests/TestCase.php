<?php

namespace Plugrbase\StatamicNotionConnector\Tests;

use Plugrbase\StatamicNotionConnector\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}

<?php

namespace Plugrbase\StatamicNotionConnector\Tests;

use Plugrbase\StatamicNotionConnector\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}

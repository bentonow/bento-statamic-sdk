<?php

namespace Bento\BentoStatamic\Tests;

use Bento\BentoStatamic\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}

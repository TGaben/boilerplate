<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\BoilerplateTestHelpers;
use Tests\Traits\FilamentTestHelpers;

abstract class TestCase extends BaseTestCase
{
    use BoilerplateTestHelpers;
    use FilamentTestHelpers;
}

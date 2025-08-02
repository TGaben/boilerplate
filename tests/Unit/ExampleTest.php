<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    #[Test]
    public function test_basic_assertion(): void
    {
        $result = 2 + 2;
        $this->assertEquals(4, $result);
    }
}

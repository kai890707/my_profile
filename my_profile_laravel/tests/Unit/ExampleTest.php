<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_addition(): void
    {
        $result = 1 + 1;
        $this->assertSame(2, $result);
    }
}

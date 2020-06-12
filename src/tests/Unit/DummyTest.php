<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    public function testDummyAssertions() {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertNull(null);
    }
}

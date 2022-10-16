<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class LoadJsonTest extends TestCase
{

    public function testLoad()
    {
        include_once __DIR__ . '/MyPredefinedFilter.php';
        $df = Profile::fromJson(__DIR__ . '/fixtures/def.json');
        $this->assertFalse($df->check(['attrib2' => 'u-123']));
        $this->assertTrue($df->check(['attrib1' => 'u-123', 'attrib2' => 'xx']));
    }

}
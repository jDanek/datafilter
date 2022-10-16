<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{


    public function testGetData()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => function ($in) {
                    return $in === 'bar';
                },
                'attrib3' => false,
                'attrib4' => function ($in) {
                    return $in === 'argl';
                },
            ],

        ]);
        $res = $df->run([
            'attrib1' => 'foo',
            'attrib2' => 'bar',
            'attrib3' => 'yadda',
            'attrib4' => 'huh',
            'attrib5' => 'wtf'
        ]);

        $dataValid = $res->getValidData();
        $this->assertEquals('foo:bar:yadda', implode(':', array_values($dataValid)));

        $dataInvalid = $res->getInvalidData();
        $this->assertEquals('huh', implode(':', array_values($dataInvalid)));

        $dataUnknown = $res->getUnknownData();
        $this->assertEquals('wtf', implode(':', array_values($dataUnknown)));

        $dataAll = $res->getAllData();
        $this->assertEquals('foo:bar:yadda:huh:wtf', implode(':', array_values($dataAll)));
    }


}



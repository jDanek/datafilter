<?php

namespace DataFilter;

use DataFilter\Util\Util;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{

    public function testMultiLevel()
    {
        $df = new Profile([
            'attributes' => [
                'level1' => false,
                'Object1.level2' => false,
                'Object2.Sub1.level3' => false,
                'Object3.Sub2.SubSub2.level4' => false
            ],
        ]);
        $res = $df->run([
            'level1' => 'l1',
            'Object1' => [
                'level2' => 'l2'
            ],
            'Object2' => [
                'Sub1' => [
                    'level3' => 'l3'
                ]
            ],
            'Object3' => [
                'Sub2' => [
                    'SubSub2' => [
                        'level4' => 'l4'
                    ]
                ]
            ]
        ]);
        $this->assertEquals(
            '{"level1":"l1","Object1.level2":"l2","Object2.Sub1.level3":"l3","Object3.Sub2.SubSub2.level4":"l4"}',
            json_encode($res->getAllData())
        );
    }

    public function testMultiLevel2()
    {
        Util::$FLATTEN_SEPARATOR = '::';
        $df = new Profile([
            'attributes' => [
                'level1' => false,
                'Object1::level2' => false,
                'Object2::Sub1::level3' => false,
                'Object3::Sub2::SubSub2::level4' => false
            ],
        ]);
        $res = $df->run([
            'level1' => 'l1',
            'Object1' => [
                'level2' => 'l2'
            ],
            'Object2' => [
                'Sub1' => [
                    'level3' => 'l3'
                ]
            ],
            'Object3' => [
                'Sub2' => [
                    'SubSub2' => [
                        'level4' => 'l4'
                    ]
                ]
            ]
        ]);
        $this->assertEquals(
            '{"level1":"l1","Object1::level2":"l2","Object2::Sub1::level3":"l3","Object3::Sub2::SubSub2::level4":"l4"}',
            json_encode($res->getAllData())
        );
    }

}
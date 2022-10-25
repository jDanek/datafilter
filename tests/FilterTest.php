<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{

    public function testGlobalFilters()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => false
            ],
            'preFilters' => [
                function ($in) {
                    return 'X' . $in;
                }
            ],
            'postFilters' => [
                function ($in) {
                    return $in . 'Y';
                }
            ]
        ]);
        $res = $df->run(['attrib1' => 'foo', 'attrib2' => 'bar', 'attrib3' => 'unknown']);
        $this->assertFalse($res->hasError());
        $data = $res->getAllData();
        $this->assertFalse(empty($data));
        $this->assertTrue(isset($data['attrib1']) && isset($data['attrib2']) && isset($data['attrib3']));
        $this->assertEquals(
            'XfooY:XbarY:Xunknown',
            $data['attrib1'] . ':' . $data['attrib2'] . ':' . $data['attrib3']
        );
    }

    public function testAttribFilters()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'preFilters' => [
                        function ($in) {
                            return 'X' . $in;
                        }
                    ],
                    'postFilters' => [
                        function ($in) {
                            return $in . 'Y';
                        }
                    ]
                ],
                'attrib2' => [
                    'postFilters' => [
                        function ($in) {
                            return $in . 'Y';
                        }
                    ]
                ]
            ],

        ]);
        $res = $df->run(['attrib1' => 'foo', 'attrib2' => 'bar', 'attrib3' => 'unknown']);
        $this->assertFalse($res->hasError());
        $data = $res->getAllData();
        $this->assertFalse(empty($data));
        $this->assertTrue(isset($data['attrib1']) && isset($data['attrib2']) && isset($data['attrib3']));
        $this->assertEquals(
            'XfooY:barY:unknown',
            $data['attrib1'] . ':' . $data['attrib2'] . ':' . $data['attrib3']
        );
    }

    public function testPFBasicTrim()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true
            ],
            'preFilters' => 'Trim',
        ]);
        $res = $df->run(['attrib1' => '  ok  ']);
        $data = $res->getValidData();
        $this->assertTrue(isset($data['attrib1']));
        $this->assertEquals('ok', $data['attrib1']);
    }

    public function testCustomFilter1()
    {
        include_once __DIR__ . '/MyPredefinedFilter.php';
        $df = new Profile([
            'attributes' => [
                'attrib1' => true
            ],
            'preFilters' => ['MyFilter'],
            'filterClasses' => ['\\DataFilter\\MyPredefinedFilter']
        ]);
        $res = $df->run(['attrib1' => 'howdy']);
        $data = $res->getValidData();
        $this->assertTrue(isset($data['attrib1']));
        $this->assertEquals('[howdy]', $data['attrib1']);
    }

    public function testCustomFilter2()
    {
        include_once __DIR__ . '/MyPredefinedFilter.php';
        $df = new Profile([
            'attributes' => [
                'attrib1' => true
            ],
            'preFilters' => [
                ['\\DataFilter\\MyPredefinedFilter', 'myFilter'],
                function ($in) {
                    return ">$in<";
                }
            ],
        ]);
        $res = $df->run(['attrib1' => 'howdy']);
        $data = $res->getValidData();
        $this->assertTrue(isset($data['attrib1']));
        $this->assertEquals('>[howdy]<', $data['attrib1']);
    }

}



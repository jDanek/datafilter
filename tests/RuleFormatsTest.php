<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class RuleFormatsTest extends TestCase
{

    public function testSimpleBoolFormat()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true
            ]
        ]);
        $this->assertFalse($df->check(['attrib2' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

    public function testSimpleFuncRefFormat()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => function ($in) {
                    return $in === 'bar';
                }
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

    public function testSimpleStaticCallbackFormat()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => array('\\DataFilter\\RuleFormatsTest', 'staticCallbackTest')
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

    public static function staticCallbackTest($in)
    {
        return $in === 'bar';
    }

    public function testSimpleObjCallbackFormat()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [$this, 'objectCallbackTest']
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

    public function objectCallbackTest($in)
    {
        return $in === 'bar';
    }

    public function testComplexRules1()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => ['\\DataFilter\\RuleFormatsTest', 'staticCallbackTest']
                    ]
                ]
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

    public function testComplexRules2()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => ['\\DataFilter\\RuleFormatsTest', 'staticCallbackTest']
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

}



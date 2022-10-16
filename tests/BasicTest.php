<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{

    public function testCreate()
    {
        $exception = false;
        try {
            $df = new Profile([]);
        } catch (\Exception $e) {
            error_log("Error in create: $e");
            $exception = true;
        }
        $this->assertFalse($exception);
    }

    public function testCreateAttribs()
    {
        $exception = false;
        try {
            $df = new Profile([
                'attributes' => [
                    'attrib1' => true,
                    'attrib2' => false
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error in create: $e");
            $exception = true;
        }
        $this->assertFalse($exception);
    }

    public function testFilterSimple1()
    {
        $exception = false;
        $checkRes = false;
        try {
            $df = new Profile([
                'attributes' => [
                    'attrib1' => true,
                    'attrib2' => false
                ]
            ]);
            $input = [
                'attrib1' => 'bla'
            ];
            if ($df->check($input)) {
                $checkRes = true;
            } else {
                print_r($df);
            }
        } catch (\Exception $e) {
            error_log("Error in create: $e");
            $exception = true;
        }
        $this->assertTrue(!$exception && $checkRes);
    }

    public function testFilterSimple2()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => false
            ]
        ]);
        $input = [
            'attrib2' => 'bla'
        ];
        $this->assertFalse($df->check($input));
    }

    public function testCanMissing()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => false
            ]
        ]);
        $input = [
            'attrib2' => 'bla'
        ];
        $res = $df->run($input);
        $this->assertTrue($res->hasError());
        $this->assertEquals('Attribute "attrib1" is missing', $res->getErrorTexts(' - '));
    }

    public function testCanSimpleConstraint()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => function ($input) {
                    return $input === 'bar';
                }
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
    }

}
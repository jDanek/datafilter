<?php

namespace DataFilter;

use \DataFilter\Util as U;
use PHPUnit\Framework\TestCase;

class ProgrammaticTest extends TestCase
{

    public function testManipulateRules()
    {
        $df = new Profile();

        $df->setAttribute('attrib1', false);

        // all optional
        $this->assertTrue($df->check([]));

        // one required
        $df->getAttribute('attrib1')->setRequired(true);
        $this->assertFalse($df->check([]));

        // satisfy required
        $this->assertTrue($df->check(['attrib1' => 'foo']));

        // add rule
        $df->getAttribute('attrib1')->setRule('minLength', 'LengthMin:5');
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'foobar']));

        // remove role again
        $df->getAttribute('attrib1')->removeRule('minLength');
        $this->assertTrue($df->check(['attrib1' => 'foo']));

        // remove required again
        $df->getAttribute('attrib1')->setRequired(false);
        $this->assertTrue($df->check([]));
    }

    public function testToggleFilters()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'required' => true,
                    'preFilters' => [
                        function ($in) {
                            return '>' . $in;
                        }
                    ],
                    'postFilters' => [
                        function ($in) {
                            return $in . '<';
                        }
                    ]
                ]
            ],
            'preFilters' => [
                function ($in) {
                    return '[' . $in;
                }
            ],
            'postFilters' => [
                function ($in) {
                    return $in . ']';
                }
            ]
        ]);

        $res = $df->run(['attrib1' => 'foo']);
        $this->assertFalse($res->hasError());

        $value = $res->getData('attrib1');
        $this->assertEquals('[>foo<]', $value);

        $df->getAttribute('attrib1')->setNoFilters(true);
        $res = $df->run(['attrib1' => 'foo']);
        $value = $res->getData('attrib1');
        $this->assertEquals('foo', $value);

    }


}
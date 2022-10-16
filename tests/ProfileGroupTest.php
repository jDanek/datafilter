<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class ProfileGroupTest extends TestCase
{

    public function testMultiProfile()
    {
        $sets = new ProfileGroup([
            'test1' => [
                'attributes' => [
                    'attrib1' => true,
                    'attrib2' => false
                ],
            ],
            'test2' => [
                'attributes' => [
                    'bla1' => true,
                    'bla2' => true
                ]
            ]
        ]);
        $sets->setProfile('test1');
        $this->assertTrue($sets->check(['attrib1' => 'here']));

        $sets->setProfile('test2');
        $this->assertFalse($sets->check(['attrib1' => 'here']));
        $this->assertTrue($sets->check(['bla1' => 'here', 'bla2' => 'there']));
    }


}



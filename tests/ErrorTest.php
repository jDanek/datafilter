<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{


    public function testDefault()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => function ($in) {
                    return 'x' === $in;
                }
            ]
        ]);
        $res = $df->run(['attrib2' => 'foo']);
        $this->assertEquals($res->getErrorTexts(':'), 'Attribute "attrib2" does not match "default":Attribute "attrib1" is missing');
    }

    public function testOtherDefault()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => function ($in) {
                    return 'x' === $in;
                }
            ],
            'missingTemplate' => 'Missing :attribute:',
            'errorTemplate' => 'Failed :attribute:',
        ]);
        $res = $df->run(['attrib2' => 'foo']);
        $this->assertEquals($res->getErrorTexts(':'), 'Failed attrib2:Missing attrib1');
    }

    public function testAttribOverwrite()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'required' => true,
                    'missing' => 'We are missing :attribute:'
                ],
                'attrib2' => [
                    'rules' => [
                        'isNotX' => [
                            'constraint' => function ($in) {
                                return 'x' === $in;
                            },
                            'error' => "Oops, :attribute: not X"
                        ]
                    ]
                ]
            ],
            'missingTemplate' => 'Missing :attribute:',
            'errorTemplate' => 'Failed :attribute:',
        ]);
        $res = $df->run(['attrib2' => 'foo']);
        $this->assertEquals('Oops, attrib2 not X:We are missing attrib1', $res->getErrorTexts(':'));
    }

    public function testErrorsByAttrib()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => true,
                'attrib2' => [
                    'rules' => [
                        'isNotX' => function ($in) {
                            return $in === 'x';
                        }
                    ]
                ],
                'attrib3' => false
            ],
            'missingTemplate' => 'Missing :attribute:',
            'errorTemplate' => 'Failed :attribute:',
        ]);
        $res = $df->run(['attrib2' => 'foo']);

        // get all
        $errors = $res->getAllErrors();
        $this->assertTrue(
            array_key_exists('attrib1', $errors)
            && array_key_exists('attrib2', $errors)
            && count($errors) === 2
        );
        $this->assertEquals('Missing attrib1', $errors['attrib1']);
        $this->assertEquals('Failed attrib2', $errors['attrib2']);

        // get invalid
        $errors = $res->getInvalidErrors();
        $this->assertTrue(array_key_exists('attrib2', $errors) && count(array_keys($errors)) === 1);
        $this->assertEquals('Failed attrib2', $errors['attrib2']);

        // get missing
        $errors = $res->getMissingErrors();
        $this->assertTrue(array_key_exists('attrib1', $errors) && count(array_keys($errors)) === 1);
        $this->assertEquals('Missing attrib1', $errors['attrib1']);

        // check all
        $this->assertTrue($res->hasError());

        // check single
        $this->assertTrue($res->hasError('attrib1') && $res->hasError('attrib2') && !$res->hasError('attrib3'));
    }

    public function testErrorInheritance()
    {
        $df = new Profile([
            'attributes' => [
                'attrib2' => [
                    'error' => 'From Attrib',
                    'rules' => [
                        'isNotX' => [
                            'constraint' => function ($in) {
                                return 'x' === $in;
                            },
                            'error' => 'From Rule'
                        ]
                    ]
                ],
                'attrib3' => false
            ],
            'errorTemplate' => 'From Profile',
        ]);
        $res = $df->run(['attrib2' => 'foo']);
        $errors = $res->getAllErrors();
        $this->assertEquals('From Rule', $errors['attrib2']);


        $df = new Profile([
            'attributes' => [
                'attrib2' => [

                    'rules' => [
                        'isNotX' => [
                            'constraint' => function ($in) {
                                return 'x' === $in;
                            },
                            'error' => 'From Attrib',
                        ]
                    ]
                ],
                'attrib3' => false
            ],
            'errorTemplate' => 'From Profile',
        ]);
        $res = $df->run(['attrib2' => 'foo']);
        $errors = $res->getAllErrors();
        $this->assertEquals('From Attrib', $errors['attrib2']);


        $df = new Profile([
            'attributes' => [
                'attrib2' => [
                    'rules' => [
                        'isNotX' => [
                            'constraint' => function ($in) {
                                return 'x' === $in;
                            },
                        ]
                    ]
                ],
                'attrib3' => false
            ],
            'errorTemplate' => 'From Profile',
        ]);
        $res = $df->run(['attrib2' => 'foo']);
        $errors = $res->getAllErrors();
        $this->assertEquals('From Profile', $errors['attrib2']);
    }

    public function testErrorsCallback()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'isNotX' => function ($in) {
                            return $in === 'x';
                        }
                    ]
                ],
            ],
            'errorTemplate' => function ($attribute, $rule, $val) {
                return 'Failed attr:' . $attribute . ', rule:' . $rule . ', value:' . $val;
            },
        ]);
        $res = $df->run(['attrib1' => 'foo']);

        // get all
        $errors = $res->getAllErrors();
        $this->assertTrue(
            array_key_exists('attrib1', $errors)
            && count($errors) === 1
        );
        $this->assertEquals('Failed attr:attrib1, rule:isNotX, value:foo', $errors['attrib1']);

        // get invalid
        $errors = $res->getInvalidErrors();
        $this->assertTrue(array_key_exists('attrib1', $errors) && count(array_keys($errors)) === 1);
        $this->assertEquals('Failed attr:attrib1, rule:isNotX, value:foo', $errors['attrib1']);


        // check all
        $this->assertTrue($res->hasError());

        // check single
        $this->assertTrue($res->hasError('attrib1'));


    }

}



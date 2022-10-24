<?php

namespace DataFilter;

use PHPUnit\Framework\TestCase;

class PredefinedRuleBasicTest extends TestCase
{

    public function testPRBasicAccept()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Accepted'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'yes']));
        $this->assertTrue($df->check(['attrib1' => 'y']));
        $this->assertTrue($df->check(['attrib1' => 'on']));
        $this->assertTrue($df->check(['attrib1' => '1']));
        $this->assertTrue($df->check(['attrib1' => 1]));
        $this->assertTrue($df->check(['attrib1' => true]));
        $this->assertFalse($df->check(['attrib1' => 'no']));
        $this->assertFalse($df->check(['attrib1' => 'n']));
        $this->assertFalse($df->check(['attrib1' => '0']));
        $this->assertFalse($df->check(['attrib1' => 0]));
        $this->assertFalse($df->check(['attrib1' => false]));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicArray()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Array'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => ['key' => 'value']]));
        $this->assertFalse($df->check(['attrib1' => 'string']));
        $this->assertFalse($df->check(['attrib1' => 123]));
        $this->assertFalse($df->check(['attrib1' => true]));
    }

    public function testPRBasicNumeric()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Numeric'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 123]));
        $this->assertTrue($df->check(['attrib1' => '123']));
        $this->assertTrue($df->check(['attrib1' => 123.1]));
        $this->assertTrue($df->check(['attrib1' => '123.1']));
        $this->assertFalse($df->check(['attrib1' => 'a1']));
    }

    public function testPRBasicInteger()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Integer'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 123]));
        $this->assertTrue($df->check(['attrib1' => '123']));
        $this->assertFalse($df->check(['attrib1' => 123.1]));
        $this->assertFalse($df->check(['attrib1' => '123.1']));
        $this->assertFalse($df->check(['attrib1' => 'a1']));
    }

    public function testPRBasicLength()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Length:3'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'f']));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
        $this->assertTrue($df->check(['attrib1' => 'foo']));
    }

    public function testPRBasicLengthBetween()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'LengthBetween:3:4'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'fooo']));
        $this->assertFalse($df->check(['attrib1' => 'foooo']));
        $this->assertFalse($df->check(['attrib1' => 'fo']));
    }

    public function testPRBasicLengthMin()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'LengthMin:3'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'f']));
        $this->assertTrue($df->check(['attrib1' => 'foo']));
    }

    public function testPRBasicLengthMax()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'LengthMax:3'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'fooo']));
        $this->assertTrue($df->check(['attrib1' => 'foo']));
    }

    public function testPRBasicMin()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Min:10'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 10]));
        $this->assertTrue($df->check(['attrib1' => 15]));
        $this->assertTrue($df->check(['attrib1' => '15']));
        $this->assertFalse($df->check(['attrib1' => 9]));
        $this->assertFalse($df->check(['attrib1' => '9']));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicMax()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Max:10'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 10]));
        $this->assertTrue($df->check(['attrib1' => 8]));
        $this->assertTrue($df->check(['attrib1' => '5']));
        $this->assertFalse($df->check(['attrib1' => 11]));
        $this->assertFalse($df->check(['attrib1' => '12']));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicBetween()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Between:2:5'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 2]));
        $this->assertTrue($df->check(['attrib1' => 3]));
        $this->assertTrue($df->check(['attrib1' => 5]));
        $this->assertTrue($df->check(['attrib1' => '4']));
        $this->assertFalse($df->check(['attrib1' => 1]));
        $this->assertFalse($df->check(['attrib1' => 9]));
        $this->assertFalse($df->check(['attrib1' => '12']));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicIn()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'In:foo:bar:123'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 123]));
        $this->assertTrue($df->check(['attrib1' => '123']));
        $this->assertTrue($df->check(['attrib1' => 'foo']));
        $this->assertTrue($df->check(['attrib1' => 'bar']));
        $this->assertFalse($df->check(['attrib1' => 'Foo']));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
        $this->assertFalse($df->check(['attrib1' => '234']));
    }

    public function testPRBasicContains()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Contains:baz'
                        ]
                    ]
                ],
                'attrib2' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Contains:baz:true'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'baz']));
        $this->assertTrue($df->check(['attrib1' => '12bazbar']));
        $this->assertTrue($df->check(['attrib1' => 'Baz']));
        $this->assertFalse($df->check(['attrib1' => '']));
        $this->assertFalse($df->check(['attrib1' => 'Foo']));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
        $this->assertFalse($df->check(['attrib1' => 234]));
        $this->assertFalse($df->check(['attrib1' => '234']));
        // strict mode
        $this->assertFalse($df->check(['attrib2' => 'Baz']));
    }

    public function testPRBasicIp()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Ip'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '127.0.0.1']));
        $this->assertTrue($df->check(['attrib1' => '255.255.11.135']));
        $this->assertTrue($df->check(['attrib1' => '255.255.255.255']));
        $this->assertFalse($df->check(['attrib1' => '256.256.256.256']));
        $this->assertFalse($df->check(['attrib1' => '123']));
        $this->assertFalse($df->check(['attrib1' => 123]));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicIpv4()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Ipv4'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '127.0.0.1']));
        $this->assertTrue($df->check(['attrib1' => '255.255.11.135']));
        $this->assertTrue($df->check(['attrib1' => '255.255.255.255']));
        $this->assertFalse($df->check(['attrib1' => '256.256.256.256']));
        $this->assertFalse($df->check(['attrib1' => '123']));
        $this->assertFalse($df->check(['attrib1' => 123]));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicIpv6()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Ipv6'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '::1']));
        $this->assertTrue($df->check(['attrib1' => 'fe80::1ff:fe23:4567:890a']));
        $this->assertTrue($df->check(['attrib1' => '2001:db8:85a3:8d3:1319:8a2e:370:7348']));
        $this->assertTrue($df->check(['attrib1' => 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff']));
        $this->assertFalse($df->check(['attrib1' => 'gggg:gggg:gggg:gggg:gggg:gggg:gggg:gggg']));
        $this->assertFalse($df->check(['attrib1' => '123']));
        $this->assertFalse($df->check(['attrib1' => 123]));
        $this->assertFalse($df->check(['attrib1' => 'foobar']));
    }

    public function testPRBasicEmail()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Email'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'user@example.com']));
        $this->assertTrue($df->check(['attrib1' => 'User@EXAMPLE.com']));
        $this->assertTrue($df->check(['attrib1' => 'user@exa-mple.com']));
        $this->assertTrue($df->check(['attrib1' => 'user@exa-m-ple.com']));
        $this->assertTrue($df->check(['attrib1' => 'user+foo@example.com']));
        $this->assertTrue($df->check(['attrib1' => 'user@example.sub.com']));
        $this->assertFalse($df->check(['attrib1' => 'user']));
        $this->assertFalse($df->check(['attrib1' => 'user@localhost']));
        $this->assertFalse($df->check(['attrib1' => 'user@...localhost']));
        $this->assertFalse($df->check(['attrib1' => 'user@example..com']));
    }

    public function testPRBasicAscii()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Ascii'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'qwertzuiop']));
        $this->assertFalse($df->check(['attrib1' => 'đ€Ł']));
        $this->assertFalse($df->check(['attrib1' => 'ěščřžýáíé']));
        $this->assertFalse($df->check(['attrib1' => '♠️ ♣️ ♥️ ♦️']));
    }

    public function testPRBasicAlpha(){
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Alpha'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'qwertzuiop']));
        $this->assertFalse($df->check(['attrib1' => 'lorem ipsum']));
        $this->assertFalse($df->check(['attrib1' => 'lorem-ipsum']));
        $this->assertFalse($df->check(['attrib1' => 'lorem_ipsum']));
        $this->assertFalse($df->check(['attrib1' => 'foo1']));
    }

    public function testPRBasicAlphaNum(){
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Alphanum'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 123]));
        $this->assertTrue($df->check(['attrib1' => '123']));
        $this->assertTrue($df->check(['attrib1' => 'a1']));
        $this->assertTrue($df->check(['attrib1' => 'A1']));
        $this->assertFalse($df->check(['attrib1' => 'a-1']));
    }

    public function testPRBasicSlug(){
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Slug'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'foo-bar']));
        $this->assertTrue($df->check(['attrib1' => 'foo--bar']));
        $this->assertTrue($df->check(['attrib1' => 'foo_bar']));
        $this->assertTrue($df->check(['attrib1' => 'foo__bar']));
        $this->assertTrue($df->check(['attrib1' => 'foo-bar-123']));
        $this->assertTrue($df->check(['attrib1' => 'foo_bar_123']));
        $this->assertTrue($df->check(['attrib1' => 'foo-bar_123']));
        $this->assertFalse($df->check(['attrib1' => 'foo bar']));
        $this->assertFalse($df->check(['attrib1' => 'foo.bar']));
        $this->assertFalse($df->check(['attrib1' => 'foo/bar']));
        $this->assertFalse($df->check(['attrib1' => 'foo&bar']));
    }

    public function testPRBasicDate()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Date'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '2012-01-01']));
        $this->assertTrue($df->check(['attrib1' => '2012-02-01']));
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertFalse($df->check(['attrib1' => '2012-02-30']));
        $this->assertFalse($df->check(['attrib1' => '2012-02-40']));
        $this->assertFalse($df->check(['attrib1' => '2012-01-01 20:00:01']));
    }

    public function testPRBasicDateFormat()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'DateFormat:Y-m-d'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '2012-01-01']));
        $this->assertTrue($df->check(['attrib1' => '2012-02-01']));
        $this->assertFalse($df->check(['attrib1' => '2012.02.01']));
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertFalse($df->check(['attrib1' => '2012-02-30']));
        $this->assertFalse($df->check(['attrib1' => '2012-02-40']));
        $this->assertFalse($df->check(['attrib1' => '2012-01-01 20:00:01']));
    }

    public function testPRBasicRegex()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Regex:/^f:o/'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertFalse($df->check(['attrib1' => 'bar']));
        $this->assertFalse($df->check(['attrib1' => 'barf']));
        $this->assertTrue($df->check(['attrib1' => 'f:oo']));
        $this->assertTrue($df->check(['attrib1' => 'f:obar']));
    }

    public function testPRBasicTime()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'Time'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '23:10']));
        $this->assertTrue($df->check(['attrib1' => '23:10:20']));
        $this->assertFalse($df->check(['attrib1' => 'foo']));
        $this->assertFalse($df->check(['attrib1' => '2012-01-01']));
    }

    public function testPRBasicDateTime()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'DateTime'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => '2012-01-01']));
        $this->assertTrue($df->check(['attrib1' => '2012-01-01 23:10:20']));
        $this->assertFalse($df->check(['attrib1' => 'foo']));
    }

    public function testPRBasicUrlPart()
    {
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'UrlPart'
                        ]
                    ]
                ]
            ]
        ]);
        $this->assertTrue($df->check(['attrib1' => 123]));
        $this->assertTrue($df->check(['attrib1' => '1-2-A']));
        $this->assertTrue($df->check(['attrib1' => '1.a~3']));
        $this->assertFalse($df->check(['attrib1' => 'a--1']));
        $this->assertFalse($df->check(['attrib1' => '-a-1']));
        $this->assertFalse($df->check(['attrib1' => 'a-1-']));
    }

    public function testPRBasicCustomRuleClass()
    {
        include_once __DIR__ . '/MyPredefinedRule.php';
        $df = new Profile([
            'attributes' => [
                'attrib1' => [
                    'rules' => [
                        'rule1' => [
                            'constraint' => 'MyRule'
                        ]
                    ]
                ]
            ],
            'ruleClasses' => [\MyPredefinedRule::class]
        ]);
        $this->assertTrue($df->check(['attrib1' => 'ok']));
        $this->assertFalse($df->check(['attrib1' => 'other']));
    }
}



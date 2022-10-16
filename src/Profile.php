<?php

namespace DataFilter;

/**
 * Filtering data

<code>

$df = new \DataFilter\Profile([
    'attribs' => [
        'attribName' => [

            // make required
            'required' => true,

            // enable match any mode (default: match all) -> as long as ONE rules matches, validation OK
            'matchAny' => true,

            // default value, if not given (implies optional)
            'default' => 'a1',

            // custom error when missing (only if no default and required)
            'missing' => 'Where is :attrib:?',

            // rules are applied as given
            'rules' => [

                // user defined callback (func ref)
                'someCallback' => [
                    'constraint' => function($input, $attrib, $rule, $dataFilter) {
                        error_log("I am in rule ". $rule->getName(). " for attrib ". $attrib->getName());
                        return strlen($input) < 5;
                    },
                    'error'      => 'Input for :attrib: is to long',
                ],

                // user defined callback (callable)
                'someCallback' => [
                    'constraint' => ['\\MyClass', 'myMethod'],
                    'error'      => 'Something is wrong with :attrib:',

                    // if this rule matches -> no other is required
                    'sufficient' => true,
                ],

                // using regex
                'someRegex' => [
                    'constraint' => 'Regex:/^a[0-9]+$/',
                    'error'      => 'String format should be "a" followed by numbers for :attrib:',
                ],

                // shortcut with no custom error
                'otherRule' => function($input) {
                    return time() % 2;
                },

            ],

            // make other attribs required via dependencies
            'dependent' => [

                // if "a123" matches
                'a123' => ['otherAttrib', 'yetAnother'],

                // if no other matches => this matches
                '*' => ['otherAttrib']
            ],

            // make other attribs required via dependencies (regex)
            'dependentRegex' => [
                '/^(a1)[234]/' => ['otherAttrib'],
            ],

            // pre-validation input filters
            'preFilters' => [
                function($input, $attrib) {
                    return $input . '0';
                }
            ],

            // post-validation input filters
            'postFilters' => [
                function($input, $attrib) {
                    return $input . '0';
                }
            ]
        ],

        // an optional attrib with a constraint
        'fooBar' => 'Regex:/^foo/',

        // a required attrib, no validation
        'otherAttrib' => true,

        // an optional attrib, no validation
        'yetAnother' => false
    ],

    // default error template
    'errorTemplate' => 'Attribute ":attrib:" is frong (rule :rule:)',

    // classes for predefined (string) rules
    'ruleClasses' => ['\\MyPredefinedRules'],

    /// classes for predefined (string) filters
    'filterClasses' => ['\\MyPredefinedFilters'],

    // global pre filters to be applied on all inputs -> run before
    'preFilters' => [
        ['\\MyClass', 'filterMethod'],
    ],

    // global post filters to be applied on all inputs (including unknown but not invalid)
    'postFilters' => [
        ['\\MyClass', 'filterMethod'],
    ]

]);

$inputData = [
];

if ($df->check($inputData)) {
    echo "OK, all good\n";
}
else {
    $res = $df->getLastResult();
    foreach ($res->getErrors() as $error) {
        echo "Err: $error\n";
    }

    if ($res->getErrorFor('attr'
}

</code>
 */
class Profile extends Filterable
{
    const DEFAULT_ERROR = 'Attribute ":attrib:" does not match ":rule:"';
    const DEFAULT_MISSING = 'Attribute ":attrib:" is missing';

    /** @var array  */
    protected $attribs=[];
    /** @var array  */
    protected $predefinedRuleClasses = [
        PredefinedRules\Basic::class
    ];
    /** @var array  */
    protected $predefinedFilterClasses = [
        PredefinedFilters\Basic::class
    ];
    /** @var string  */
    protected $errorTemplate = self::DEFAULT_ERROR;
    /** @var string  */
    protected $missingTemplate = self::DEFAULT_MISSING;
    /** @var Result */
    protected $lastResult;

    public function __construct(array $definition = [])
    {
        if (isset($definition['errorTemplate'])) {
            $this->errorTemplate = $definition['errorTemplate'];
        }
        if (isset($definition['missingTemplate'])) {
            $this->missingTemplate = $definition['missingTemplate'];
        }
        foreach (['ruleClasses', 'filterClasses'] as $var) {
            if (isset($definition[$var])) {
                $accessor = 'predefined'. ucfirst($var);
                foreach ($definition[$var] as $addClass) {
                    array_push($this->{$accessor}, $addClass);
                }
                array_unique($this->$accessor);
            }
        }
        if (isset($definition['preFilters'])) {
            $this->addFilters('pre', $definition['preFilters']);
        }
        if (isset($definition['postFilters'])) {
            $this->addFilters('post', $definition['postFilters']);
        }
        if (isset($definition['attribs'])) {
            $this->setAttribs($definition['attribs']);
        } elseif (isset($definition['attributes'])) {
            $this->setAttribs($definition['attributes']);
        } else {
            $this->attribs = [];
        }
    }

    /**
     * Construct from JSON
     * @param string $json  The JSON or path to JSON file
     * @throws \RuntimeException
     */
    public static function fromJson(string $json): Profile
    {
        // handle file
        $content = '';
        if (is_file($json)) {
            if (is_file($json) && !is_readable($json)) {
                throw new \RuntimeException("Either '$json' is not a file or cannot access it");
            }
            $content = file_get_contents($json);
            if (!$content) {
                throw new \RuntimeException("Cannot load empty JSON file '$json'");
            }
        }
        // handle string
        $jsonArr = json_decode($content, true);
        if (!$jsonArr) {
            throw new \RuntimeException("Could not parse JSON");
        }
        return new Profile($jsonArr);
    }

    /**
     * Set (replace/add) multiple named attribs at once
     * @param array $definition  Attrib/rule definition
     */
    public function setAttribs(array $definition): void
    {
        foreach ($definition as $name => $def) {
            $this->setAttrib($name, $def);
        }
    }


    /**
     * Set (replace/add) a named attribute. Returns the new attrib
     * @param mixed   $definition  Attrib/rule definition or \DataFilter\Attribute object
     */
    public function setAttrib(string $name, $definition = null): Attribute
    {
        $this->attribs[$name] = is_object($definition) && $definition instanceof Attribute
            ? $definition
            : new Attribute($name, $definition, $this);
        return $this->attribs[$name];
    }

    /**
     * Returns list of attributes (assoc array)
     */
    public function getAttribs(): array
    {
        return $this->attribs;
    }

    /**
     * Returns single attribute by name (or null)
     */
    public function getAttrib(string $name): ?Attribute
    {
        return $this->attribs[$name] ?? null;
    }

    /**
     * Removes a single attribute by name
     */
    public function removeAttrib(string $name): bool
    {
        if (isset($this->attribs[$name])) {
            unset($this->attribs[$name]);
            return true;
        }
        return false;
    }

    /**
     * Returns list of predefined rule classes
     */
    public function getPredefinedRuleClasses(): array
    {
        return $this->predefinedRuleClasses;
    }

    /**
     * Returns list of predefined filter classes
     */
    public function getPredefinedFilterClasses(): array
    {
        return $this->predefinedFilterClasses;
    }

    /**
     * Returns default error template
     */
    public function getErrorTemplate(): string
    {
        return $this->errorTemplate;
    }

    /**
     * Returns default missing template
     */
    public function getMissingTemplate(): string
    {
        return $this->missingTemplate;
    }

    /**
     * Returns the last check result
     */
    public function getLastResult(): Result
    {
        return $this->lastResult;
    }

    /**
     * Check this rule against input
     * @return bool
     */
    public function run(array $data)
    {
        $this->lastResult = new Result($this);
        $this->lastResult->check($data);
        return $this->lastResult;
    }

    /**
     * Check this rule against input
     */
    public function check(array $data): bool
    {
        return !$this->run($data)->hasError();
    }

}

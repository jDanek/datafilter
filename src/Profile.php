<?php

namespace DataFilter;

/**
 * Filtering data

<code>

$df = new \DataFilter\Profile([
    'attributes' => [
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
                        error_log("I am in rule ". $rule->getName(). " for attribute ". $attrib->getName());
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

            // make other attributes required via dependencies
            'dependent' => [

                // if "a123" matches
                'a123' => ['otherAttrib', 'yetAnother'],

                // if no other matches => this matches
                '*' => ['otherAttrib']
            ],

            // make other attributes required via dependencies (regex)
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

        // an optional attribute with a constraint
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
    public const DEFAULT_ERROR = 'Attribute ":attribute:" does not match ":rule:"';
    public const DEFAULT_MISSING = 'Attribute ":attribute:" is missing';

    /** @var array */
    protected $attributes = [];
    /** @var array */
    protected $predefinedRuleClasses = [
        Predefined\Rule::class
    ];
    /** @var array */
    protected $predefinedFilterClasses = [
        Predefined\Filter::class
    ];
    /** @var string|callable */
    protected $errorTemplate = null;
    /** @var string|callable */
    protected $missingTemplate = null;
    /** @var Result */
    protected $lastResult;

    public function __construct(array $definition = [])
    {
        $this->resolveTemplates($definition);
        $this->resolveClassMap($definition);
        $this->resolveFilters($definition);
        $this->resolveAttributes($definition);
    }

    /**
     * Construct from JSON
     * @param string $json The JSON or path to JSON file
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
     * Set (replace/add) multiple named attributes at once
     * @param array $definition Attrib/rule definition
     */
    public function setAttributes(array $definition): void
    {
        foreach ($definition as $name => $def) {
            $this->setAttribute($name, $def);
        }
    }

    /**
     * Set (replace/add) a named attribute. Returns the new attrib
     * @param mixed $definition Attrib/rule definition or \DataFilter\Attribute object
     */
    public function setAttribute(string $name, $definition = null): Attribute
    {
        $this->attributes[$name] = $definition instanceof Attribute
            ? $definition
            : new Attribute($name, $definition, $this);
        return $this->attributes[$name];
    }

    /**
     * Returns list of attributes (assoc array)
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns single attribute by name (or null)
     */
    public function getAttribute(string $name): ?Attribute
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Removes a single attribute by name
     */
    public function removeAttribute(string $name): bool
    {
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
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
     * @return string|callable
     */
    public function getError(string $default = null)
    {
        return $this->errorTemplate ?? $default ?? self::DEFAULT_ERROR;
    }

    /**
     * Returns default missing template
     * @return string|callable
     */
    public function getMissingTemplate(string $default = null)
    {
        return $this->missingTemplate ?? $default ?? self::DEFAULT_MISSING;
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
     */
    public function run(array $data): Result
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


    protected function resolveTemplates(array $definition): void
    {
        if (isset($definition['errorTemplate'])) {
            $this->errorTemplate = $definition['errorTemplate'];
        }
        if (isset($definition['missingTemplate'])) {
            $this->missingTemplate = $definition['missingTemplate'];
        }
    }

    protected function resolveClassMap(array $definition): void
    {
        foreach (['ruleClasses', 'filterClasses'] as $var) {
            if (isset($definition[$var])) {
                $accessor = 'predefined' . ucfirst($var);
                foreach ($definition[$var] as $addClass) {
                    array_push($this->{$accessor}, $addClass);
                }
                array_unique($this->{$accessor});
            }
        }
    }

    protected function resolveFilters(array $definition): void
    {
        if (isset($definition['preFilters'])) {
            $this->addFilters(Filterable::POSITION_PRE, $definition['preFilters']);
        }
        if (isset($definition['postFilters'])) {
            $this->addFilters(Filterable::POSITION_POST, $definition['postFilters']);
        }
    }

    protected function resolveAttributes(array $definition): void
    {
        if (isset($definition['attributes'])) {
            $this->setAttributes($definition['attributes']);
        }
    }

}

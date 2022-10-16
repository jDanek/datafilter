<?php

namespace DataFilter;

/**
 * Data attribute
 *
 * Attributes are named input parameters with validation rules and filters
 */
class Rule
{

    /** @var array  */
    protected static $DEFAULT_ATTRIBS = [
        'sufficient' => false,
        'skipEmpty'  => false,
        'constraint' => null,
        'error'      => null,
    ];
    /** @var Profile */
    protected $dataFilter;
    /** @var Attribute */
    protected $attrib;
    /** @var string */
    protected $name;
    /** @var callable (func ref) */
    protected $constraint;
    /** @var string */
    protected $error;
    /** @var bool */
    protected $sufficient = false;
    /** @var bool */
    protected $skipEmpty = false;
    /** @var bool  */
    protected $lazy = false;

    /**
     * @var bool
     */
    protected $definition = null;
    /** @var string */
    protected $lastValue;

    /**
     * @param mixed $definition  The rule definition
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, $definition, Attribute $attribute, Profile $dataFilter)
    {
        $this->name = $name;
        $this->attrib = $attribute;
        $this->dataFilter = $dataFilter;

        if (is_array($definition) && isset($definition['lazy']) && $definition['lazy'] === true) {
            $this->lazy = $definition['lazy'];
            $this->definition = $definition;
        }
        else {
            $this->parseDefinition($definition);
        }
    }

    /**
     * The long description
     * @param mixed  $definition  The rule definition
     * @throws \InvalidArgumentException
     */
    protected function parseDefinition($definition = null)
    {
        if (is_null($definition)) {
            if (!is_null($this->definition)) {
                throw new \InvalidArgumentException(
                    'Cannot parse rule definitions for rule "'. $this->name. '", attrib "'
                    . $this->attrib->getName(). '" without definition!'
                );
            }
            $definition = $this->definition;
            $this->definition = null;
        }

        // required, simple
        if (is_string($definition) || is_callable($definition)) {
            $definition = ['constraint' => $definition];
        }

        // init empty to reduce isset checks..
        $definition = array_merge(self::$DEFAULT_ATTRIBS, $definition);

        // set attribs
        $this->sufficient = $definition['sufficient'];
        $this->skipEmpty  = $definition['skipEmpty'];
        $this->error      = $definition['error'];

        // having old style callable constraint
        if (is_callable($definition['constraint']) && is_array($definition['constraint'])) { // !($definition['constraint'] instanceof \Closure)) {
            $cb = $definition['constraint'];
            $definition['constraint'] = function () use ($cb) {
                $args = func_get_args();
                return call_user_func_array($cb, $args);
            };
        }

        // from string -> check predefined
        elseif (is_string($definition['constraint'])) {
            $args = preg_split('/:/', $definition['constraint']);
            $method = 'rule'. array_shift($args);
            $found = false;
            foreach ($this->dataFilter->getPredefinedRuleClasses() as $className) {
                if (is_callable([$className, $method]) && method_exists($className, $method)) {
                    $definition['constraint'] = call_user_func_array([$className, $method], $args);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new \InvalidArgumentException(
                    'Could not use constraint "'. $definition['constraint']. '" for rule "'
                    . $this->name. '", attrib "'. $this->attrib->getName(). '" because no '
                    . 'predefined rule class found implementing "'. $method. '()"'
                );
            }
        }

        // determine class
        $constraintClass = is_object($definition['constraint'])
            ? get_class($definition['constraint'])
            : '(Scalar)';

        // at this point: it has to be a closure!
        if ($constraintClass !== 'Closure') {
            throw new \InvalidArgumentException(
                'Definition for rule "'. $this->name. '", attrib "'. $this->attrib->getName(). '"'
                . ' has an invalid constraint of class '. $constraintClass
            );
        }
        $this->constraint = $definition['constraint'];
    }

    /**
     * Check this rule against input
     */
    public function check(string $input): bool
    {
        if ($this->lazy) {
            $this->lazy = false;
            $this->parseDefinition();
        }
        $this->lastValue = $input;
        if (strlen($input) === 0 && $this->skipEmpty) {
            return true;
        }
        $constraint = $this->constraint;
        return $constraint($input, $this, $this->attrib, $this->dataFilter);
    }

    /**
     * Returns last input value used for check (or determine Dependent)
     */
    public function getLastValue(): string
    {
        return $this->lastValue;
    }

    /**
     * Returns bool whether this is sufficient
     */
    public function isSufficient(): bool
    {
        return $this->sufficient;
    }

    /**
     * Returns error string or null
     */
    public function getError(Attribute $attrib = null): ?string
    {
        if ($this->error == false) {
            return null;
        }
        if (!$attrib) {
            $attrib = $this->attrib;
        }
        $formatData = ['rule' => $this->name];
        if ($attrib) {
            $formatData['attrib'] = $attrib->getName();
        }
        $error = $this->error;
        if (!$error && $attrib) {
            $error = $attrib->getDefaultErrorStr();
        }
        if (!$error) {
            $error = $this->dataFilter->getErrorTemplate();
        }
        return Util::formatString($error, $formatData);
    }

}

<?php

namespace DataFilter;

use DataFilter\Util\Util;

/**
 * Data attribute
 *
 * Attributes are named input parameters with validation rules and filters
 */
class Rule
{

    /** @var array  */
    protected static $DEFAULT_ATTRIBUTES = [
        'sufficient' => false,
        'skipEmpty'  => false,
        'constraint' => null,
        'error'      => null,
    ];
    /** @var Profile */
    protected $dataFilter;
    /** @var Attribute */
    protected $attribute;
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
        $this->attribute = $attribute;
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
                throw new \InvalidArgumentException(sprintf(
                    "Cannot parse rule definitions for rule '%s', attribute '%s' without definition!",
                    $this->name,
                    $this->attribute->getName()
                ));
            }
            $definition = $this->definition;
            $this->definition = null;
        }

        // required, simple
        if (is_string($definition) || is_callable($definition)) {
            $definition = ['constraint' => $definition];
        }

        // init empty to reduce isset checks..
        $definition = array_merge(self::$DEFAULT_ATTRIBUTES, $definition);

        // set attributes
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
                throw new \InvalidArgumentException(sprintf(
                    "Could not use constraint '%s' for rule '%s', attribute '%s' because no predefined rule class found implementing '%s()'",
                    $definition['constraint'],
                    $this->name,
                    $this->attribute->getName(),
                    $method
                ));
            }
        }

        // determine class
        $constraintClass = is_object($definition['constraint'])
            ? get_class($definition['constraint'])
            : '(Scalar)';

        // at this point: it has to be a closure!
        if ($constraintClass !== 'Closure') {
            throw new \InvalidArgumentException(sprintf(
                "Definition for rule '%s', attribute '%s' has an invalid constraint of class '%s'",
                $this->name,
                $this->attribute->getName(),
                $constraintClass
            ));
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
        return $constraint($input, $this, $this->attribute, $this->dataFilter);
    }

    public function getName(): string
    {
        return $this->name;
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
            $attrib = $this->attribute;
        }
        $formatData = ['rule' => $this->name];
        if ($attrib) {
            $formatData['attribute'] = $attrib->getName();
        }
        $error = $this->error;
        if (!$error && $attrib) {
            $error = $attrib->getDefaultErrorStr();
        }
        if (!$error) {
            $error = $this->dataFilter->getError();
            if (is_callable($error) || is_array($error)) {
                if (is_array($error) && !method_exists($error[0], $error[1])) {
                    throw new \InvalidArgumentException("Invalid callback definition");
                }
                $error = call_user_func_array($error, [
                    $this->attribute->getName(),
                    $this->name,
                    $this->getLastValue(),
                ]);
            }
        }
        return Util::formatString($error, $formatData);
    }

}

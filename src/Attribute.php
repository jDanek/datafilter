<?php

namespace DataFilter;

use DataFilter\Util\Util;

/**
 * Data attribute
 *
 * Attributes are named input parameters with validation rules and filters
 */
class Attribute extends Filterable
{

    /** @var array */
    protected static $DEFAULT_ATTRIBUTES = [
        'required' => false,
        'matchAny' => false,
        'default' => null,
        'missing' => null,
        'error' => null,
        'rules' => [],
        'dependent' => [],
        'dependentRegex' => [],
        'preFilters' => [],
        'postFilters' => [],
    ];

    /** @var Profile */
    protected $dataFilter;
    /** @var string */
    protected $name;
    /** @var bool */
    protected $required = false;
    /** @var bool */
    protected $matchAny = false;
    /** @var bool */
    public $noFilters = false;
    /** @var string */
    public $default = null;
    /** @var string */
    public $missing = null;
    /** @var string */
    public $error = null;
    /** @var array<string, Rule> */
    protected $rules = [];
    /** @var array */
    protected $dependent = [];
    /** @var array */
    protected $dependentRegex = [];
    /** @var Rule */
    protected $failedRule;
    /** @var string */
    protected $lastValue;

    /**
     * @param callable|string|bool|null $definition The definition (containing rule and stuff)
     */
    public function __construct(string $name, $definition, Profile $dataFilter)
    {
        $this->name = $name;
        $this->dataFilter = $dataFilter;

        // no definition
        if (is_null($definition)) {
            // nothing
        }

        // required, simple
        elseif (is_bool($definition)) {
            $this->required = $definition;
        }

        // complex..
        else {

            // from string or callable (simple, optional)
            if (is_string($definition) || is_callable($definition)) {
                $definition = ['rules' => ['default' => $definition]];
            }

            // init empty to reduce isset checks...
            $definition = array_merge(self::$DEFAULT_ATTRIBUTES, $definition);

            // set attributes
            foreach (['required', 'matchAny', 'noFilters', 'default', 'dependent', 'dependentRegex', 'missing', 'error'] as $k) {
                if (isset($definition[$k])) {
                    $this->{$k} = $definition[$k];
                }
            }

            // add all rules
            $this->setRules($definition['rules']);

            // add all filter
            $this->addFilters(Filterable::POSITION_PRE, $definition['preFilters']);
            $this->addFilters(Filterable::POSITION_POST, $definition['postFilters']);
        }
    }

    /**
     * Set (replace/add) multiple rules at once
     */
    public function setRules(array $rules): void
    {
        foreach ($rules as $ruleName => $rule) {
            $this->setRule($ruleName, $rule);
        }
    }

    /**
     * Set (replace/add) a single named rule. Returns the new rule
     * @param mixed $definition the rule definition or a \DataFilter\Rule object
     */
    public function setRule(string $name, $definition): Rule
    {
        $this->rules[$name] = $definition instanceof Rule
            ? $definition
            : new Rule($name, $definition, $this, $this->dataFilter);
        return $this->rules[$name];
    }

    /**
     * List of all rules as array
     * @return array<string, Rule>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Get a single rule by name (or null)
     */
    public function getRule(string $name): ?Rule
    {
        return $this->rules[$name] ?? null;
    }

    /**
     * Removes a single rule by name
     */
    public function removeRule(string $name): bool
    {
        if (isset($this->rules[$name])) {
            unset($this->rules[$name]);
            return true;
        }
        return false;
    }

    /**
     * Check all rules of this attribute against input
     */
    public function check(string $input): bool
    {
        $this->lastValue = $input;
        $anyFailed = false;
        foreach ($this->rules as $name => &$rule) {

            // at least OK
            if ($rule->check($input)) {

                // stop here if any is OK or rule is sufficient
                if ($this->matchAny || $rule->isSufficient()) {
                    return true;
                }
            }

            // if not in match any mode -> first fail stops
            elseif (!$this->matchAny) {
                $this->failedRule = &$rule;
                return false;
            }

            // at least one failed
            else {
                if (!$anyFailed) {
                    $this->failedRule = &$rule;
                }
                $anyFailed = true;
            }
        }

        // all have to work out!
        return !$anyFailed;
    }

    /**
     * Adds possible requires to list if. Only if not have error.
     */
    public function determineDependents($input, array &$required): void
    {
        if ($this->hasError()) {
            return;
        }
        $this->lastValue = $input;

        // check all simple dependents
        $foundRequired = false;
        foreach ($this->dependent as $onInput => $requiredNames) {
            if ($onInput === '*') {
                continue;
            }
            elseif ($input === $onInput) {
                $foundRequired = true;
                foreach ($requiredNames as $attribName) {
                    $required[$attribName] = true;
                }
            }
        }

        // the default dependent does apply if no simple were found and input is given (not empty)
        if ($input && !$foundRequired && isset($this->dependent['*'])) {
            foreach ($this->dependent['*'] as $attribName) {
                $required[$attribName] = true;
            }
        }

        // apply regex dependent
        foreach ($this->dependentRegex as $onRegex => $requiredNames) {
            if (preg_match($onRegex, $input)) {
                foreach ($requiredNames as $attribName) {
                    $required[$attribName] = true;
                }
            }
        }
    }

    /**
     * Returns attribute name
     */
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
     * Returns error
     */
    public function getError(): ?string
    {
        if ($this->failedRule) {
            return $this->failedRule->getError($this);
        }
        return null;
    }

    /**
     * Returns default error string
     */
    public function getDefaultErrorStr(): ?string
    {
        return $this->error;
    }

    /**
     * Whether any has failed
     */
    public function hasError(): bool
    {
        return (bool)$this->failedRule;
    }

    public function getErrorText(): string
    {
        $name = $this->name;
        $ruleName = $this->failedRule->getName();
        $value = $this->failedRule->getLastValue();

        // process Closure
        $error = $this->dataFilter->getError($this->getError());
        if (is_callable($error) || is_array($error)) {
            if (is_array($error) && !method_exists($error[0], $error[1])) {
                throw new \InvalidArgumentException("Invalid callback definition");
            }
            $error = call_user_func_array($error, [$name, $ruleName, $value]);
        }

        return Util::formatString($error, [
            'attribute' => $name,
            'rule' => $ruleName,
            'value' => $value,
        ]);
    }

    /**
     * Returns formatted missing text
     */
    public function getMissingText(): string
    {
        $name = $this->name;

        // process Closure
        $missing = $this->dataFilter->getMissingTemplate($this->missing);
        if (is_callable($missing) || is_array($missing)) {
            if (is_array($missing) && !method_exists($missing[0], $missing[1])) {
                throw new \InvalidArgumentException("Invalid callback definition");
            }
            $missing = call_user_func_array($missing, [$name]);
        }

        return Util::formatString($missing, [
            'attribute' => $name,
        ]);
    }

    /**
     * Returns default value (or null)
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * Returns whether required
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Returns whether filters are enabled to use
     */
    public function useFilters(): bool
    {
        return !$this->noFilters;
    }

    /**
     * Sets required mode
     */
    public function setRequired(bool $mode = true): void
    {
        $this->required = $mode;
    }

    /**
     * Sets matchAny mode
     */
    public function setMatchAny(bool $mode = true): void
    {
        $this->matchAny = $mode;
    }

    /**
     * Sets noFilters mode
     */
    public function setNoFilters(bool $mode = true): void
    {
        $this->noFilters = $mode;
    }

    /**
     * Sets default string (or null)
     */
    public function setDefault(?string $default = null): void
    {
        $this->default = $default;
    }

    /**
     * Sets missing template (or null)
     */
    public function setMissing(?string $template = null): void
    {
        $this->missing = $template;
    }

    /**
     * Resets check results
     */
    public function reset(): void
    {
        $this->failedRule = null;
    }

}

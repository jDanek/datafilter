<?php

namespace DataFilter;

/**
 * Data attribute
 *
 * Attributes are named input parameters with validation rules and filters
 */
class Result
{

    /** @var Profile  */
    protected $dataFilter;
    /** @var array  */
    protected $validAttributes = [];
    /** @var array  */
    protected $invalidAttributes = [];
    /** @var array  */
    protected $missingAttributes = [];
    /** @var array  */
    protected $unknownAttributes = [];

    /**
     * @param Profile $dataFilter  Parental data filter
     */
    public function __construct(Profile $dataFilter)
    {
        $this->dataFilter = $dataFilter;
    }

    /**
     * Returns all validated attributes
     *
     * @return array
     <code>
     $res = [
        'attribName' => (\DataFilter\Attribute)$obj,
        // ..
     ];
     </code>
     */
    public function getValidAttributes(): array
    {
        return array_combine(
            array_keys($this->validAttributes),
            array_map(function ($ref) {
                return $ref['attribute'];
            }, array_values($this->validAttributes))
        );
    }

    /**
     * Returns all validated data
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The input data',
        // ..
     ];
     </code>
     */
    public function getValidData(): array
    {
        return array_combine(
            array_keys($this->validAttributes),
            array_map(function ($ref) {
                return $ref['value'];
            }, array_values($this->validAttributes))
        );
    }

    /**
     * Returns all invalidated attributes
     *
     * @return array
     <code>
     $res = [
        'attribName' => (\DataFilter\Attribute)$obj,
        // ..
     ];
     </code>
     */
    public function getInvalidAttributes(): array
    {
        return array_combine(
            array_keys($this->invalidAttributes),
            array_map(function ($ref) {
                return $ref['attribute'];
            }, array_values($this->invalidAttributes))
        );
    }

    /**
     * Returns all invalid input data
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The input value',
        // ..
     ];
     </code>
     */
    public function getInvalidData(): array
    {
        return array_combine(
            array_keys($this->invalidAttributes),
            array_map(function ($ref) {
                return $ref['value'];
            }, array_values($this->invalidAttributes))
        );
    }

    /**
     * Returns all errors for invalid attributes
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The Error message',
        // ..
     ];
     </code>
     */
    public function getInvalidErrors(): array
    {
        return array_combine(
            array_keys($this->invalidAttributes),
            array_map(function ($ref) {
                return $ref['error'];
            }, array_values($this->invalidAttributes))
        );
    }

    /**
     * Returns all missing attributes
     *
     * @return array
     <code>
     $res = [
        'attribName' => (\DataFilter\Attribute)$obj,
        // ..
     ];
     </code>
     */
    public function getMissingAttributes(): array
    {
        return array_combine(
            array_keys($this->missingAttributes),
            array_map(function ($ref) {
                return $ref['attribute'];
            }, array_values($this->missingAttributes))
        );
    }

    /**
     * Returns all missing error message
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The error message'
        // ..
     ];
     </code>
     */
    public function getMissingErrors(): array
    {
        return array_combine(
            array_keys($this->missingAttributes),
            array_map(function ($ref) {
                return $ref['error'];
            }, array_values($this->missingAttributes))
        );
    }

    /**
     * Returns combined missing an invalid error messages
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The error message'
        // ..
     ];
     </code>
     */
    public function getInvalidOrMissingErrors(): array
    {
        $all = $this->invalidAttributes + $this->missingAttributes;
        return array_combine(
            array_keys($all),
            array_map(function ($ref) {
                return $ref['error'];
            }, array_values($all))
        );
    }

    /**
     * Returns data of unknown input
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The input data',
        // ..
     ];
     </code>
     */
    public function getUnknownData(): array
    {
        return $this->unknownAttributes;
    }

    /**
     * Returns data of all inputs (valid, invalid and unknown input)
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The input data',
        // ..
     ];
     </code>
     */
    public function getAllData(): array
    {
        return $this->getValidData() + $this->getInvalidData() + $this->unknownAttributes;
    }

    /**
     * Returns attribute of named attribute if existing (either valid or invalid)
     *
     * @param string $attribName  Name of the attrib
     *
     * @return Attribute
     */
    public function getAttribute(string $attribName): ?Attribute
    {
        if (isset($this->validAttributes[$attribName])) {
            return $this->validAttributes[$attribName]['attribute'];
        }
        elseif (isset($this->invalidAttributes[$attribName])) {
            return $this->invalidAttributes[$attribName]['attribute'];
        }
        return null;
    }

    /**
     * Returns value of named attribute if existing (either valid, invalid or unknown)
     * @return string
     */
    public function getData(string $attribName): ?string
    {
        if (isset($this->validAttributes[$attribName])) {
            return $this->validAttributes[$attribName]['value'];
        }
        elseif (isset($this->invalidAttributes[$attribName])) {
            return $this->invalidAttributes[$attribName]['value'];
        }
        elseif (isset($this->unknownAttributes[$attribName])) {
            return $this->unknownAttributes[$attribName];
        }
        return null;
    }


    /**
     * Returns whether has error
     */
    public function hasError(string $attribName = null): bool
    {
        if (is_null($attribName)) {
            return count($this->invalidAttributes) > 0 || count($this->missingAttributes) > 0;
        } else {
            return isset($this->invalidAttributes[$attribName]) || isset($this->missingAttributes[$attribName]);
        }
    }

    /**
     * Returns all errors (invalid + missing)
     *
     * @return array
     <code>
     $res = [
        'attribName' => 'The error text',
        // ..
     ];
     </code>
     */
    public function getAllErrors(): array
    {
        return $this->getInvalidErrors() + $this->getMissingErrors();
    }

    /**
     * Returns all error texts (no assoc)
     *
     * @return array|string
     <code>
     $res = [
        'The error text',
        // ..
     ];
     </code>
     */
    public function getErrorTexts($join = null)
    {
        $errors = array_filter(
            array_merge(array_values($this->getInvalidErrors()), array_values($this->getMissingErrors())),
            function($txt) {
                return !is_null($txt);
            }
        );
        return $join ? implode($join, $errors) : $errors;
    }


    /**
     * Check this rule against input
     */
    public function check(array $data): bool
    {
        $this->validAttributes   = [];
        $this->invalidAttributes = [];
        $this->missingAttributes = [];
        $this->unknownAttributes = [];
        $requiredDependent    = [];
        $seenAttrib           = [];

        foreach (Util::flatten($data) as $attributeName => $value) {
            $attribute = $this->dataFilter->getAttribute($attributeName);
            if (!$attribute) {
                $parts = explode(Util::$FLATTEN_SEPARATOR, $attributeName);
                $count = count($parts);
                if ($count > 1) {

                    for ($i = $count -1; $i >= 1; $i--) {
                        $testName = implode(Util::$FLATTEN_SEPARATOR, array_splice($parts, 0, $i));
                        $attribute   = $this->dataFilter->getAttribute($testName. Util::$FLATTEN_SEPARATOR. '*');
                        if ($attribute) {
                            break;
                        }
                    }
                }
            }
            $seenAttrib[$attributeName] = true;

            // unknown attrib
            if (!$attribute) {
                $this->unknownAttributes[$attributeName] = $this->dataFilter->applyFilter(Filterable::POSITION_PRE, $value);
                continue;
            }

            // run pre-filters
            if ($attribute->useFilters()) {
                $value = $this->dataFilter->applyFilter(Filterable::POSITION_PRE, $attribute->applyFilter(Filterable::POSITION_PRE, $value));
            }

            // successful check
            if ($attribute->check($value)) {
                $this->validAttributes[$attributeName] = [
                    'value'  => $attribute->useFilters()
                        ? $this->dataFilter->applyFilter(Filterable::POSITION_POST, $attribute->applyFilter(Filterable::POSITION_POST, $value))
                        : $value,
                    'attribute' => &$attribute
                ];

                // determine possible dependents
                $attribute->determineDependents($value, $requiredDependent);
            }

            // checks failed
            else {
                $this->invalidAttributes[$attributeName] = [
                    'value'  => $value,
                    'attribute' => &$attribute,
                    'error'  => $attribute->getError()
                ];
            }
        }

        // check now all attributes for required
        foreach ($this->dataFilter->getAttributes() as $attributeName => $attribute) {

            // already seen
            if (isset($seenAttrib[$attributeName])) {
                continue;
            }

            // has default
            elseif (!is_null($default = $attribute->getDefault())) {
                $this->validAttributes[$attributeName] = [
                    'value' => $default,
                    'attribute' => &$attribute
                ];
            }

            // required -> missing
            elseif ($attribute->isRequired() || isset($requiredDependent[$attributeName])) {
                $parts = explode(Util::$FLATTEN_SEPARATOR, $attributeName);
                $count = count($parts);
                if ($count > 1 && $parts[$count-1] === '*') {
                    $before = implode(Util::$FLATTEN_SEPARATOR, array_splice($parts, 0, $count - 1)) . Util::$FLATTEN_SEPARATOR;
                    $seen   = array_filter(array_keys($seenAttrib), function ($check) use ($before) {
                        return strpos($check, $before) === 0;
                    });
                    if (count($seen) > 0) {
                        continue;
                    }
                }
                $this->missingAttributes[$attributeName] = [
                    'attribute' => &$attribute,
                    'error'  => $attribute->getMissingText()
                ];
            }
        }
        return !$this->hasError();
    }

}

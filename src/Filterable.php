<?php

namespace DataFilter;

/**
 * Basic predefined validation rules
 */
abstract class Filterable
{
    public const POSITION_PRE = 'pre';
    public const POSITION_POST = 'post';

    /** @var array */
    protected $preFilters = [];
    /** @var array */
    protected $postFilters = [];

    /**
     * Add multiple filters at once
     *
     * @param string $position see Filterable::POSITION_*
     * @param callable|array $filters List of filters
     *
     * @throws \InvalidArgumentException
     */
    public function addFilters(string $position, $filters): void
    {
        // oops, invalid position
        if (!in_array($position, [self::POSITION_PRE, self::POSITION_POST])) {
            throw new \InvalidArgumentException(sprintf(
                "Cannot add filters to '%s'. Use '%s' or '%s'",
                $position,
                self::POSITION_PRE,
                self::POSITION_POST
            ));
        }

        // single filter
        if (is_callable($filters)) {
            $filters = [$filters];
        }

        // determine accessor
        $accessor = $position . 'Filters';
        if (!$this->{$accessor}) {
            $this->{$accessor} = [];
        }

        // add all filters
        foreach ($filters as $num => $filter) {

            // callable, not closure
            if (is_callable($filter) && is_array($filter)) { // && !($filter instanceof \Closure)) {
                $cb = $filter;
                $filter = function ($in) use ($cb) {
                    return call_user_func_array($cb, [$in]);
                };
            } // from string (predefined filter)
            elseif (is_string($filter)) {
                $method = 'filter' . $filter;
                $df = $this instanceof Profile ? $this : $this->dataFilter;
                $foundFilter = false;
                $args = $this instanceof Profile
                    ? [null, $this]               // data filter
                    : [$this, $this->dataFilter]; // attribute

                foreach ($df->getPredefinedFilterClasses() as $className) {
                    if (is_callable($className, $method) && method_exists($className, $method)) {
                        $foundFilter = true;
                        $filter = call_user_func_array([$className, $method], $args);
                        break;
                    }
                }
                if (!$foundFilter) {
                    $filterName = $this instanceof Profile
                        ? 'global ' . $position . '-filter'
                        : 'rule "' . $this->name . '", attribute "' . $this->attrib->getName() . '"'
                        . ' as ' . $position . '-filter';

                    throw new \InvalidArgumentException(sprintf(
                        "Could not use filter '%s' for '%s' because no predefined filter class found implementing '%s()'",
                        $filter,
                        $filterName,
                        $method
                    ));
                }
            }

            // oops, invalild filter
            if (!is_callable($filter)) {
                throw new \InvalidArgumentException(sprintf(
                    "Filter '%d' for attribute '%s' is not a callable!",
                    $num,
                    $this->name
                ));
            }
            // convert oldschool filter to closure
            if (!($filter instanceof \Closure)) {
                $args = $this instanceof Profile
                    ? [null, $this]               // data filter
                    : [$this, $this->dataFilter]; // attribute
                $filter = call_user_func_array($filter, $args);
            }

            // add filter
            array_push($this->{$accessor}, $filter);
        }
    }

    /**
     * Add multiple pre-filters at once
     * @throws \InvalidArgumentException
     */
    public function addPreFilters(array $filters): void
    {
        $this->addFilters(self::POSITION_PRE, $filters);
    }

    /**
     * Add multiple post-filters at once
     * @throws \InvalidArgumentException
     */
    public function addPostFilters(array $filters): void
    {
        $this->addFilters(self::POSITION_POST, $filters);
    }


    /**
     * Runs filter on input
     *
     * @param string $position see Filterable::POSITION_*
     * @param string $input The input
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function applyFilter(string $position, string $input): string
    {
        // oops, invalid position
        if (!in_array($position, [self::POSITION_PRE, self::POSITION_POST])) {
            throw new \InvalidArgumentException(sprintf(
                "Cannot add filters to '%s'. Use '%s' or '%s'",
                $position,
                self::POSITION_PRE,
                self::POSITION_POST
            ));
        }

        // determine accessor
        $accessor = $position . 'Filters';

        if (!$this->{$accessor}) {
            return $input;
        }

        foreach ($this->{$accessor} as $filter) {
            $input = $filter($input);
        }
        return $input;
    }

}

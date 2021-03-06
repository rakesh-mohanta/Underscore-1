<?php

namespace Underscore;

/**
 * Class Underscore
 * @package Underscore
 * @SuppressWarnings(TooManyMethods) - these methods belong here
 */
class Underscore
{
    /** @var  Collection */
    protected $wrapped;

    /**
     * Initializes Underscore object and sets argument as internal collection
     *
     * @param mixed $item
     * @return Underscore
     */
    public static function from($item)
    {
        $underscore = new Underscore();

        $underscore->wrap($item);

        return $underscore;
    }

    /**
     * @param mixed $item
     */
    protected function wrap($item)
    {
        $this->wrapped = new Collection($item);
    }

    /**
     * Returns object
     *
     * @return mixed
     */
    public function value()
    {
        if ($this->wrapped instanceof Collection) {
            return $this->wrapped->value();
        } else {
            return $this->wrapped;
        }
    }

    /**
     * Returns object as array
     *
     * @return mixed[]
     */
    public function toArray()
    {
        return $this->wrapped->toArray();
    }

    /**
     * Call $iterator for each element
     *
     * $iterator = function($value, $key, $collection)
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function invoke($iterator)
    {
        foreach ($this->wrapped as $k => $v) {
            call_user_func($iterator, $v, $k, $this->wrapped);
        }

        return $this;
    }

    /**
     * Replaces every element with value returned by individual $iterator call
     *
     * $iterator = function($value, $key, $collection)
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function map($iterator)
    {
        $collection = clone $this->wrapped;

        foreach ($collection as $k => $v) {
            $collection[$k] = call_user_func($iterator, $v, $k, $collection);
        }

        $this->wrapped = $collection;

        return $this;
    }

    /**
     * Reduces collection to single value using $iterator
     *
     * $iterator = function($accumulator, $value)
     *
     * @param \Closure $iterator
     * @param mixed    $initial
     * @return Underscore
     */
    public function reduce($iterator, $initial = null)
    {
        $collection = clone $this->wrapped;

        foreach ($collection as $value) {
            $initial = call_user_func($iterator, $initial, $value);
        }

        $this->wrapped = $initial;

        return $this;
    }

    /**
     * Reduces collection to single value using $iterator. Reversed direction.
     *
     * $iterator = function($accumulator, $value)
     *
     * @param \Closure $iterator
     * @param mixed    $initial
     * @return Underscore
     */
    public function reduceRight($iterator, $initial = null)
    {
        $collection = clone $this->wrapped;

        foreach ($collection->getIteratorReversed() as $value) {
            $initial = call_user_func($iterator, $initial, $value);
        }

        $this->wrapped = $initial;

        return $this;
    }

    /**
     * Serves as shorthand to get list of specific key value from every element
     *
     * If key not found returns null
     *
     * @param mixed $key
     * @return Underscore
     */
    public function pick($key)
    {
        return $this->map(
            function ($value) use ($key) {
                if (is_object($value)) {
                    if (is_callable(array($value, $key))) {
                        return call_user_func(array($value, $key));
                    } else {
                        return isset($value->{$key}) ? $value->{$key} : null;
                    }
                } else {
                    return isset($value[$key]) ? $value[$key] : null;
                }
            }
        );
    }

    /**
     * Checks if a given value is present in a collection using strict equality for comparisons.
     *
     * Returns bool
     *
     * @param mixed $needle
     * @return Underscore
     */
    public function contains($needle)
    {
        $finder = function ($needle) {
            return function ($value) use ($needle) {
                return $value === $needle;
            };
        };

        $this->find($finder($needle));

        return $this;
    }

    /**
     * Iterates over elements of a collection, returning an array of all elements the callback returns truey for.
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function filter($iterator)
    {
        $collection = clone $this->wrapped;

        foreach ($this->wrapped as $k => $v) {
            if (!call_user_func($iterator, $v, $k)) {
                unset($collection[$k]);
            }
        }

        $this->wrapped = $collection;

        return $this;
    }

    /**
     * The opposite of filter(). This method returns the elements of a collection that the callback
     * does **not** return truey for.
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function reject($iterator)
    {
        $collection = clone $this->wrapped;

        foreach ($this->wrapped as $k => $v) {
            if (call_user_func($iterator, $v, $k)) {
                unset($collection[$k]);
            }
        }

        $this->wrapped = $collection;

        return $this;
    }

    /**
     * Checks if the $iterator returns a truey value for ANY element of a collection.
     * The function returns as soon as it finds a passing value and does not iterate
     * over entire collection.
     *
     * Returns boolean
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function any($iterator)
    {
        $collection = clone $this->wrapped;

        $found = false;
        foreach ($collection as $k => $v) {
            if (call_user_func($iterator, $v, $k)) {
                $found = true;
                break;
            }
        }

        $this->wrapped = $found;

        return $this;
    }

    /**
     * Checks if the $iterator returns a truey value for ALL element of a collection.
     *
     * Returns boolean
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function all($iterator)
    {
        $this->reduce(
            function ($accumulator, $item) use ($iterator) {
                $accumulator = $accumulator && $iterator($item);
                return $accumulator;
            },
            true
        );

        return $this;
    }

    /**
     * Iterates over elements of a collection, returning the first element that the callback returns truey for.
     *
     * Returns mixed
     *
     * @param \Closure $iterator
     * @return Underscore
     */
    public function find($iterator)
    {
        $collection = clone $this->wrapped;

        $found = false;
        foreach ($collection as $k => $v) {
            if (call_user_func($iterator, $k, $v, $collection)) {
                $found = true;
                break;
            }
        }
        $this->wrapped = $found;

        return $this;
    }

    /**
     * Gets the size of the collection by returning length for arrays or number of enumerable properties for objects.
     *
     * Returns int
     *
     * @return Underscore
     */
    public function size()
    {
        return $this->wrapped->count();
    }

    /**
     * Gets the first element or first n elements of collection.
     *
     * Returns mixed[]
     *
     * @param int $count
     *
     * @return Underscore
     */
    public function head($count = 1)
    {
        $this->wrapped = array_slice($this->wrapped->toArray(), 0, $count);

        return $this;
    }

    /**
     * Gets the last element or last n elements of collection.
     *
     * Returns mixed[]
     *
     * @param int $count
     *
     * @return Underscore
     */
    public function last($count = 1)
    {
        $this->wrapped = array_slice($this->wrapped->toArray(), -$count);

        return $this;
    }

    /**
     * Gets all but the first element or first n elements of collection.
     *
     * Returns mixed[]
     *
     * @param int $count
     *
     * @return Underscore
     */
    public function tail($count = 1)
    {
        $this->wrapped = array_slice($this->wrapped->toArray(), $count);

        return $this;
    }

    /**
     * Gets all but the last element or last n elements of collection.
     *
     * Returns mixed[]
     *
     * @param int $count
     *
     * @return Underscore
     */
    public function initial($count = 1)
    {
        $this->wrapped = array_slice($this->wrapped->toArray(), 0, -$count);

        return $this;
    }

    /**
     * Removes all falsey values.
     *
     * @return Underscore
     */
    public function compact()
    {
        $this->filter(
            function ($item) {
                return $item;
            }
        );
        return $this;
    }

    /**
     * Removes all provided values using strict comparison.
     *
     * @param mixed[] $values
     *
     * @return Underscore
     */
    public function without($values = array())
    {
        $this->reject(
            function ($item) use ($values) {
                return in_array($item, $values, true);
            }
        );
        return $this;
    }

    /**
     * Merges two collections. If keys collide, new value overwrites older.
     *
     * @param Underscore $values
     *
     * @return Underscore
     */
    public function merge(Underscore $values)
    {
        foreach ($values->wrapped as $key => $value) {
            $this->wrapped[$key] = $value;
        }

        return $this;
    }

    /**
     * Creates an collection composed of the enumerable property values of object.
     *
     * @return Underscore
     */
    public function values()
    {
        $collection = array();

        foreach ($this->wrapped as $value) {
            $collection[] = $value;
        }

        $this->wrapped = self::from($collection);

        return $this;
    }

    /**
     * Creates an collection composed of the enumerable property keys of object.
     *
     * @return Underscore
     * @SuppressWarnings(UnusedLocalVariable) - $value in foreach
     */
    public function keys()
    {
        $collection = array();

        foreach ($this->wrapped as $key => $value) {
            $collection[] = $key;
        }

        $this->wrapped = self::from($collection);

        return $this;
    }

    /**
     * Clones makes clone of collection
     *
     * @return Underscore
     */
    public function clon()
    {
        return self::from(unserialize(serialize($this->wrapped->value())));
    }

    /**
     * Combines current collection values with given keys to produce new collection
     *
     * @param mixed[] $keys
     *
     * @throws \LogicException
     * @return Underscore
     */
    public function zip($keys)
    {
        $values = $this->values()->toArray();
        $keys = self::from($keys)->values()->toArray();

        if (count($values) !== count($keys)) {
            throw new \LogicException('Keys and values count must match');
        }

        $collection = array();
        foreach ($values as $index => $value) {
            $collection[$keys[$index]] = $value;
        }

        $this->wrap($collection);

        return $this;
    }

    /**
     * Creates an object composed of keys generated from the results
     * of running each element of a collection through the callback
     *
     * @param \Closure $callback
     *
     * @return Underscore
     */
    public function groupBy($callback)
    {
        $collection = clone $this->wrapped;

        $result = array();
        foreach ($collection as $value) {
            $key            = call_user_func($callback, $value);
            $result[$key][] = $value;
        }

        $this->wrap($result);

        return $this;
    }
}

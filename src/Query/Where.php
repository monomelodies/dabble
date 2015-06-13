<?php

/**
 * Object for generating the WHERE part of a query.
 *
 * @package Dabble
 * @subpackage Query
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Query;

use PDO;
use PDOException;
use PDOStatement;
use ArrayObject;

class Where implements Bindable
{
    use Value;

    protected $bound = [];
    protected $where;

    /**
     * Constants for aiding in interval statements.
     * {{{
     */
    const YEAR = 1;
    const MONTH = 2;
    const WEEK = 3;
    const DAY = 4;
    const HOUR = 5;
    const MINUTE = 6;
    const SECOND = 7;
    /** }}} */

    public function __construct(array $where = null, $separator = 'AND')
    {
        if ($where) {
            $this->where = $this->prepareBindings($where);
        }
        $this->separator = $separator;
    }

    public function prepareBindings(array $where)
    {
        if (!$where) {
            return false;
        }
        foreach ($where as $key => $value) {
            if (is_numeric($key)) {
                $where[$key] = new Where(
                    $value,
                    $this->separator == 'AND' ? 'OR' : 'AND'
                );
                $this->bound = array_merge(
                    $this->bound,
                    $where[$key]->getBindings()
                );
            } elseif (is_array($value)) {
            } else {
                $where[$key] = $this->value($value);
            }
        }
        return $where;
    }

    public function getBindings()
    {
        return $this->bound;
    }

    public function __toString()
    {
        if (!$this->where) {
            return '(1=1)';
        }
        foreach ($this->where as $key => $value) {
            if (is_numeric($key)) {
                $array[$key] = $value->__toString();
            } elseif (is_array($value)) {
                $keys = array_keys($value);
                $mod = array_shift($keys);
                switch (strtoupper($mod)) {
                    case 'BETWEEN':
                        $vals = array_shift($value);
                        $array[$key] = sprintf(
                            "($key BETWEEN %s AND %s)",
                            $this->quote(array_shift($vals)),
                            $this->quote(array_shift($vals))
                        );
                        break;
                    case 'IN':
                    case 'NOT IN':
                        $array[$key] = $this->in(
                            $key,
                            $value[$mod],
                            strtoupper($mod),
                            $bind
                        );
                        break;
                    case 'ANY':
                        $array[$key] = $this->any(
                            $key,
                            array_unique($value[$mod]),
                            $bind
                        );
                        break;
                    case 'LIKE':
                        $array[$key] = sprintf(
                            "(%s LIKE %s OR %s LIKE %s OR %s LIKE %s)",
                            $key,
                            $this->quote("%{$value[$mod]}"),
                            $key,
                            $this->quote("{$value[$mod]}%"),
                            $key,
                            $this->quote("%{$value[$mod]}%")
                        );
                        break;
                    default:
                        $val = array_shift($value);
                        $array[$key] = sprintf(
                            '%s %s %s',
                            $key,
                            $this->operator($val, $mod),
                            $this->value($val)
                        );
                }
            } else {
                $array[$key] = sprintf(
                    '%s %s %s',
                    $key,
                    $this->operator($value),
                    $this->value($value)
                );
            }
        }
        return '('.implode(" {$this->separator} ", $array).')';
    }

    public function in($key, $values, $operator, &$bind)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        $values = array_unique($values);
        return sprintf(
            '%s %s (%s)',
            $key,
            $operator,
            implode(', ', $this->values($values, $bind))
        );      
    }

    public function operator($value, $operator = '=')
    {
        if ($value === null) {
            return $operator == '=' ? 'IS' : 'IS NOT';
        }
        if ($operator === '!') {
            return '<>';
        }
        if (is_numeric($operator)) {
            return '=';
        }
        return $operator;
    }
}

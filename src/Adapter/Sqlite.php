<?php

/**
 * Database abstraction layer for SqLite.
 *
 * @package Dabble
 * @subpackage Adapter
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2015
 */

namespace Dabble\Adapter;

use Dabble\Adapter;
use Dabble\Query\Raw;

/** SqLite-abstraction class. */
class Sqlite extends Adapter
{
    public function __construct(
        $dsn,
        $username = null,
        $password = null,
        array $options = []
    ) {
        return parent::__construct(
            "sqlite:$dsn",
            $username,
            $password,
            $options
        );
    }

    public function value($value)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return parent::value($value);
    }

    public function interval($quantity, $amount)
    {
        $what = null;
        switch ($quantity) {
            case self::SECOND: $what = 'second'; break;
            case self::MINUTE: $what = 'minute'; break;
            case self::HOUR: $what = 'hour'; break;
            case self::DAY: $what = 'day'; break;
            case self::WEEK: $what = 'week'; break;
            case self::MONTH: $what = 'month'; break;
            case self::YEAR: $what = 'year'; break;
        }
        return sprintf("interval %d %s", $amount, $what);
    }

    public function random()
    {
        return 'RANDOM()';
    }

    public function now()
    {
        return new Raw('CURRENT_TIMESTAMP');
    }
}


<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use DB;

class Cases implements Contracts\Endpoint, Contracts\QueryApplier
{
    protected $params;

    public function __construct(array $params) {
        $this->params = $params;
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        return new static($options);
    }

    public function apply(Builder $query, array $settings): void {
        $cases  = $settings['cases'] ?? null;
        $params = $this->params;

        $quote  = true;
        if (isset($params['_quote'])) {
            $quote = intval($params['_quote']) ? true : false;
            unset($params['_quote']);
        }

        $concat = null;
        if (isset($params['_concat'])) {
            $concat = $params['_concat'];
            unset($params['_concat']);
        }

        $explode    = $params['_explode'] ?? ',';
        unset($params['_explode']);

        if ($cases) foreach ($params as $name => $values) {
            if ($values === \luc\UNIT) {
                continue;
            }

            $values = $explode ? explode($explode, $values) : [$values];
            if ($values[0] === \luc\UNIT) {
                continue;
            }

            $assign = [];
            foreach ($values as $value) {
                $assign[] = $quote ? DB::getPdo()->quote($value) : $value;
            }

            $concat && $assign = [implode($concat, $assign)];

            $case   = $cases[$name];

            foreach ($case as $sql) {
                if (is_array($sql)) {
                    if (isset($sql['when'])) {
                        $when = $sql['when'];
                        if (!$when(...$values)) {
                            continue;
                        }
                    }

                    $sql = $sql['sql'];
                }

                $sql = \luc\padding($sql, $assign);
                $query->where(function($query) use ($sql) {
                    $query->whereRaw($sql);
                });
            }
        }
    }
}
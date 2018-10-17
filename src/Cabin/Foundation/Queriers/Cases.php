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

        if ($cases) foreach ($params as $name => $values) {
            if ($values === \luc\UNIT) {
                continue;
            }

            $assign = [];
            foreach (explode(',', $values) as $value) {
                $assign[] = $quote ? DB::getPdo()->quote($value) : $value;
            }

            $case   = $cases[$name];

            foreach ($case as $sql) {
                $sql = \luc\padding($sql, $assign);
                $query->whereRaw($sql);
            }
        }
    }
}
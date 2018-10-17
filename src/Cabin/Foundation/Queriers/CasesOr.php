<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use DB;

class CasesOr implements Contracts\Endpoint, Contracts\QueryApplier
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

    /**
     * 这里sqlList不再合并，而是每个预设query内部实现or条件。
     * 一次casesOr后面跟多个预设query时相互之间仍是and关系。
     *
     * @param Builder $query
     * @param array $settings
     * @return void
     */
    public function apply(Builder $query, array $settings): void {
        $cases  = $settings['casesOr'] ?? null;
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

            $sqlList = [];
            foreach ($case as $sql) {
                $sqlList[] = \luc\padding($sql, $assign);
            }
            $query->where(function($query) use ($sqlList) {
                foreach ($sqlList as $sql) {
                    $query->orWhereRaw($sql);
                }
            });
        }
    }
}
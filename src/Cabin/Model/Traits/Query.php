<?php

namespace Luclin\Cabin\Model\Traits;

use Luclin\Contracts;
use Luclin\Luri\Preset;

use Illuminate\Database\Eloquent\Builder;

trait Query
{

    /**
     * query
     *
     * @param string|\Luclin\Contracts\QueryApplier $appliers
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query(...$appliers)
    {
        if (isset($appliers[0]) && $appliers[0] instanceof Builder) {
            $query = array_shift($appliers);
        } else {
            $query = (new static)->newQuery();
        }
        if ($appliers) {
            foreach ($appliers as $applier) {
                if (is_string($applier)) {
                    $applier = new Preset($applier, static::_preset());
                }
                if ($applier instanceof Preset) {
                    $parsed = $applier->parse();
                    if (!$parsed) {
                        throw new \UnexpectedValueException("The query preset not exists.");
                    }
                    foreach ($parsed as $endpoint) {
                        if ($endpoint instanceof Contracts\QueryApplier) {
                            $endpoint->apply($query, static::_query());
                        }
                    }
                } else {
                    $applier->apply($query, static::_query());
                }
            }
        }
        return $query;
    }

    protected static function _preset(): array {
        return [];
    }

    protected static function _query(): array {
        return [];
    }
}
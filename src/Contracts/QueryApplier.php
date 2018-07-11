<?php

namespace Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface QueryApplier
{
    public function apply(Builder $query, array $settings): void;
}
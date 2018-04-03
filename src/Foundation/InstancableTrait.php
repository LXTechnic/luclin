<?php

namespace Luclin\Foundation;

trait InstancableTrait
{
    public static function instance(...$arguments) {
        return new static(...$arguments);
    }
}
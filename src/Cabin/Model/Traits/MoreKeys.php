<?php

namespace Luclin\Cabin\Model\Traits;

use Luclin\Contracts;
use Luclin\Luri\Preset;

use Illuminate\Database\Eloquent\Builder;

trait MoreKeys
{
    public static function found($feature, bool $reload = false, array $extra = []) {
        if (!is_array($feature)) {
            $id      = explode(',', $feature);
            $feature = static::defaultKeys();
            $count   = 0;
            foreach ($feature as $field => $default) {
                isset($id[$count]) && $feature[$field] = $id[$count];
                $count++;
            }
        }
        return parent::found($feature, $reload, $extra);
    }

    abstract protected static function defaultKeys(): array;
}
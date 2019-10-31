<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property array $body
 * @property string $mark
 * @property array $details
 */
trait Extendible
{
    private $withoutDetailLimit = false;

    protected static function migrateUpExtendible(Blueprint $table): void
    {
        $table->jsonb('body')->nullable()->comment('主数据');
        $table->string('mark', 250)->nullable()->comment('注释');
        $table->jsonb('details')->nullable()->comment('扩展数据');
    }

    protected static function migrateDownExtendible(Blueprint $table): void
    {
        $table->dropColumn('body');
        $table->dropColumn('mark');
        $table->dropColumn('details');
    }

    public function setDetailsAttribute(array $values) {
        $details = static::detailsDefault();
        $data    = $details ?: [];
        foreach ($values as $key => $value) {
            $details ? (array_key_exists($key, $details) && $data[$key] = $value) : ($data[$key] = $value);
        }
        $this->attributes['details'] = json_encode($data);
    }

    public function appendDetails(array $data): self {
        $details = $this->details ?: [];
        $details = array_merge($details, $data);
        $this->details = $details;
        return $this;
    }

    public function withoutDetailLimit(callable $func): void {
        $this->withoutDetailLimit = true;
        $func->call($this);
        $this->withoutDetailLimit = false;
    }

    public static function detailsDefault(): ?array {
        return null;
    }
}
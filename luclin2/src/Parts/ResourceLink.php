<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $resource_type
 * @property int $resource_id
 */
trait ResourceLink
{
    protected static function migrateUpResourceLink(Blueprint $table): void
    {
        $table->string('resource_type', 50)->nullable();
        $table->string('resource_id', 250)->nullable();
    }

    protected static function migrateDownResourceLink(Blueprint $table): void
    {
        $table->dropColumn('resource_type');
        $table->dropColumn('resource_id');
    }

    public static function findByResourceLink(string $type, $id): ?self {
        return self::query()->where('resource_type', $type)
            ->where('resource_id', $id)
            ->first();
    }

    public function inheritResource($parent): self {
        $this->resource_type    = $parent->resource_type;
        $this->resource_id      = $parent->resource_id;
        return $this;
    }

    public function setResource(string $type, $id): self {
        $this->resource_type = $type;
        $this->resource_id   = $id;
        return $this;
    }

    public function getResource(): array {
        return [$this->resource_type, $this->resource_id];
    }
}
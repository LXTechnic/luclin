<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;

abstract class Generated extends Model
{
    use Traits\Query;

    protected static $autoGenId = true;

    public $incrementing = false;
    protected $keyType = 'string';

    public function __construct(array $attributes = []) {
        if (static::$autoGenId) {
            $this->genId();
        }

        parent::__construct($attributes);
    }

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->string('id', 50);
        $table->integer('id_sim');
        $table->primary('id');
        $table->index('id_sim');
    }

    public function genId(): string {
        $id = $this->genPrimaryId();
        $this->id_sim = static::genSimId($id);
        return $this->setId($id)->id();
    }

    protected function genPrimaryId(): string {
        return \luc\idgen::sortedUuid();
    }

    public function setIdSimAttribute($value) {
        $this->attributes['id_sim'] = static::getStoragedIdSim($value);
    }

    public function getIdSimAttribute($value) {
        return $value !== null ? $value + 200000000 : $value;
    }

    public static function getStoragedIdSim($value): int {
        return $value - 200000000;
    }

    public static function genSimId(string $id): int {
        $gmp = gmp_init(substr($id, 5, 6), 36);
        return gmp_intval($gmp);
    }

    public static function getBySimId(int $idSim): Builder {
        return self::where('id_sim', $idSim);
    }

    public function genBarcode(int $size = 5, int $height = 100,
        string $format = 'png'): ?string
    {
        $id_sim = $this->id_sim;
        if (!$id_sim) {
            return false;
        }

        $code = str_pad("$id_sim", 12, '0', \STR_PAD_LEFT);

        $class = "Picqer\Barcode\BarcodeGenerator".strtoupper($format);
        $generator = new $class;
        return $generator->getBarcode($code,
            $generator::TYPE_CODE_128, $size, $height);
    }

    public function save(array $options = []) {
        if (!$this->id()) {
            $this->genId();
        }
        return parent::save($options);
    }

}
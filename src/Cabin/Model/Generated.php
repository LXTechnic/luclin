<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;

class Generated extends Model
{
    use Traits\Query;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->string('id', 50);
        $table->integer('id_sim');
        $table->primary('id');
        $table->index('id_sim');
    }

    protected static function migrateDownPrimary(Blueprint $table): void
    {
        $table->dropColumn('id');
        $table->dropColumn('id_sim');
    }

    protected function idPrefix(): string {
        return 'ac';
    }

    public function genId(): string {
        $id = $this->genPrimaryId();
        $this->id_sim = static::genSimId($id);
        return $this->setId($id)->id();
    }

    protected function genPrimaryId(): string {
        return \luc\idgen::sorted36();
    }

    public function setIdAttribute($value) {
        $this->attributes['id'] = $this->idPrefix()."-$value";
    }

    public function setIdSimAttribute($value) {
        $this->attributes['id_sim'] = $value - 134000000;
    }

    public function getIdSimAttribute($value) {
        return $value !== null ? $value + 134000000 : $value;
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

}
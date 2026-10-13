<?php

namespace Newus\Satusehat\Terminology;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\KodeWilayah.
 *
 * @property string $kode_wilayah
 * @property string $nama_wilayah
 * @property int $level
 * @property $parent
 * @property $state
 * @property $created_at
 * @property $updated_at
 */
class KodeWilayahIndonesia extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_master'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.kode_wilayah_indonesia_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = true; // Sesuaikan dengan 'bigIncrements' di migration

    protected $casts = [
        'kode_wilayah' => 'string',
        'nama_wilayah' => 'string',
        'level' => 'integer',
        'parent' => 'string',
        'state' => 'string',
    ];
}

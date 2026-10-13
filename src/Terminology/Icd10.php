<?php

namespace Newus\Satusehat\Terminology;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\Icd10.
 *
 * @property string $icd10_code
 * @property string $icd10_en
 * @property string $icd10_id
 * @property bool $active
 * @property $created_at
 * @property $updated_at
 */
class Icd10 extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_master'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.icd10_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $casts = ['icd10_code' => 'string', 'icd10_en' => 'string', 'icd10_id' => 'string'];
}

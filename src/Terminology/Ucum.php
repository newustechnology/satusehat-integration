<?php

namespace Newus\Satusehat\Terminology;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\Ucum.
 *
 * @property string $code
 * @property string $descriptive_name
 * @property string $code_system
 * @property string $definition
 * @property $date_created
 * @property string $synonym
 * @property string $status
 * @property string $kind_of_quantity
 * @property $date_revised
 * @property string $concept_id
 * @property string $dimension
 * @property $created_at
 * @property $updated_at
 */
class Ucum extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_master'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.ucum_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $casts = [
        'code' => 'string',
        'descriptive_name' => 'string',
        'code_system' => 'string',
        'definition' => 'string',
        'date_created' => 'datetime',
        'synonym' => 'string',
        'status' => 'string',
        'kind_of_quantity' => 'string',
        'date_revised' => 'datetime',
        'concept_id' => 'string',
        'dimension' => 'string',
    ];
}

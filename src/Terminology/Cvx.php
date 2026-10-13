<?php

namespace Newus\Satusehat\Terminology;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\Cvx.
 *
 * @property string $cvx_code
 * @property string $cvx_short_description
 * @property string $full_vaccine_name
 * @property string $note
 * @property string $vaccine_status
 * @property int $internal_id
 * @property bool $nonvaccine
 * @property $update_date
 * @property $created_at
 * @property $updated_at
 */
class Cvx extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_master'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.cvx_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $casts = [
        'cvx_code' => 'string',
        'cvx_short_description' => 'string',
        'full_vaccine_name' => 'string',
        'note' => 'string',
        'vaccine_status' => 'string',
        'internal_id' => 'integer',
        'nonvaccine' => 'boolean',
        'update_date' => 'datetime',
    ];
}

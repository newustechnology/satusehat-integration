<?php

namespace Newus\Satusehat\Terminology;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\Snomedct.
 *
 * @property string $SNOMEDCT_CODE
 * @property string $DESCRIPTION
 * @property $created_at
 * @property $updated_at
 */
class Snomedct extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_master'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.snomedct_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $casts = [];
}

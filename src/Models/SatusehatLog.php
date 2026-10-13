<?php

namespace Newus\Satusehat\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\SatusehatLog.
 *
 * @property $response_id
 * @property string $action
 * @property string $url
 * @property $payload
 * @property array $response
 * @property string $user_id
 * @property $created_at
 * @property $updated_at
 */
class SatusehatLog extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_satusehat'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.log_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'created_at';

    public $incrementing = false;

    protected $casts = ['response_id' => 'string', 'action' => 'string', 'url' => 'string',
        'payload' => 'array', 'response' => 'array', 'user_id' => 'string'];
}

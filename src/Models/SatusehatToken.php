<?php

namespace Newus\Satusehat\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\SatusehatToken.
 *
 * @property string $environment
 * @property string $token
 * @property $created_at
 * @property $updated_at
 */
class SatusehatToken extends Model
{
    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_satusehat'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.token_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'token';

    public $incrementing = false;

    protected $casts = ['environment' => 'string', 'client_id' => 'string', 'token' => 'string'];
}

<?php

namespace Newus\Satusehat\Terminology;

use Illuminate\Database\Eloquent\Model;

/**
 * Newus\Satusehat\Models\KemkesTerm.
 *
 * @property string $resource_type
 * @property string $attribute_path
 * @property string $code
 * @property string $parent_code
 * @property string $display
 * @property string $display_en
 * @property string $code_system
 * @property $created_at
 * @property $updated_at
 */
class KemkesTerm extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection_master'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.kemkesterm_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $casts = [
        'resource_type' => 'string',
        'attribute_path' => 'string',
        'code' => 'string',
        'parent_code' => 'string',
        'display' => 'string',
        'display_en' => 'string',
        'code_system' => 'string',
    ];
}

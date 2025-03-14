<?php

namespace Plugrbase\StatamicNotionConnector\Models;

use Illuminate\Database\Eloquent\Model;

class NotionMapping extends Model
{
    protected $fillable = [
        'name',
        'database_id',
        'collection_handle',
        'field_mappings',
        'mapped_pages'
    ];

    protected $casts = [
        'field_mappings' => 'array',
        'mapped_pages' => 'array'
    ];
} 
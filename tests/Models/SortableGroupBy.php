<?php

namespace LeParking\EloquentSortable\Tests\Models;

class SortableGroupBy extends Sortable
{
    protected $sortable = [
        'group_by' => 'group',
    ];
}

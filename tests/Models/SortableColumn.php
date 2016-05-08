<?php

namespace LeParking\EloquentSortable\Tests\Models;

class SortableColumn extends Sortable
{
    protected $sortable = [
        'column' => 'sort',
    ];
}

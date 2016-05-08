<?php

namespace LeParking\EloquentSortable\Tests\Models;

class SortableInsertFirst extends Sortable
{
    protected $sortable = [
        'insert_first' => true,
        'group_by' => 'group',
    ];
}

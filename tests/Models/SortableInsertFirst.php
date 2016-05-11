<?php

namespace LeParking\Sortable\Tests\Models;

class SortableInsertFirst extends Sortable
{
    protected $sortable = [
        'insert_first' => true,
        'group_by' => 'group',
    ];
}

<?php

namespace LeParking\EloquentSortable;

interface Sortable
{
    public function sortableCreating();

    public function sortableCreated();

    public function sortableDeleted();
}

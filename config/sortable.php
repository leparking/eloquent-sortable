<?php

/**
 * Configuration for the sortable package.
 * https://github.com/leparking/laravel-sortable
 */
return [
    /**
     * Name of the column that will store the position.
     */
    'column' => 'position',

    /**
     * If set to true, new models will be inserted at the first position.
     */
    'insert_first' => false,

    /**
     * A column name or an array of columns names to group sortable models.
     */
    'group_by' => false,
];

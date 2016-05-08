<?php

/**
 * Configuration for the sortable package.
 * https://github.com/leparking/eloquent-sortable
 */
return [
    /**
     * Name of the column that will store the position.
     */
    'column' => 'position',

    /**
     * Determine whether the position should be set automatically when creating
     * new sortable models.
     */
    'sort_on_create' => true,

    /**
     * Determine if other models should be reordered when deleting a sortable model.
     */
    'reorder_on_delete' => true,

    /**
     * If set to true, new models will be inserted at the first position.
     */
    'insert_first' => false,

    /**
     * A column name or an array of columns names to group sortable models.
     */
    'group_by' => false,
];

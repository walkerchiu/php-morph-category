<?php

namespace WalkerChiu\MorphCategory\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class CategoryLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.morph-category.categories_lang');

        parent::__construct($attributes);
    }
}

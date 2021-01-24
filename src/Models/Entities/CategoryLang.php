<?php

namespace WalkerChiu\MorphCategory\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class CategoryLang extends Lang
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->table = config('wk-core.table.morph-category.categories_lang');

        parent::__construct($attributes);
    }
}

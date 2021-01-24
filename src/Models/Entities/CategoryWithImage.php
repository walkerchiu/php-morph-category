<?php

namespace WalkerChiu\MorphCategory\Models\Entities;

use WalkerChiu\MorphCategory\Models\Category;
use WalkerChiu\MorphImage\Models\Entities\ImageTrait;

class CategoryWithImage extends Category
{
    use ImageTrait;
}

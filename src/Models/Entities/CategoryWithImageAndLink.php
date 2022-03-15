<?php

namespace WalkerChiu\MorphCategory\Models\Entities;

use WalkerChiu\MorphCategory\Models\Entities\Category;
use WalkerChiu\MorphImage\Models\Entities\ImageTrait;
use WalkerChiu\MorphLink\Models\Entities\LinkTrait;

class CategoryWithImageAndLink extends Category
{
    use ImageTrait;
    use LinkTrait;
}

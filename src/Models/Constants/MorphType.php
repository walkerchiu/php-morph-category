<?php

namespace WalkerChiu\MorphCategory\Models\Constants;

/**
 * @license MIT
 * @package WalkerChiu\MorphCategory
 *
 *
 */

class MorphType
{
    public static function getCodes($type)
    {
        $items = [];
        $types = self::all();

        switch ($type)
        {
            case "relation":
                foreach ($types as $key=>$value) array_push($items, $key);
            break;

            case "class":
                foreach ($types as $value) array_push($items, $value);
            break;
        }

        return $items;
    }

    public static function options($only_vaild = false)
    {
        $items = $only_vaild ? [] : ['' => trans('php-core::system.null')];

        $types = self::all();
        foreach ($types as $key=>$value) {
            $items = array_merge($items, [$key => trans('php-morph-category::system.morphType.'.$key)]);
        }

        return $items;
    }

    public static function all()
    {
        return [
            'admin'      => 'Admin',
            'api'        => 'API',
            'article'    => 'Article',
            'blog'       => 'Blog',
            'catalog'    => 'Catalog',
            'category'   => 'Category',
            'card'       => 'Card',
            'cover'      => 'Cover',
            'device'     => 'Device',
            'friendship' => 'Friendship',
            'group'      => 'Group',
            'icon'       => 'Icon',
            'image'      => 'Image',
            'level'      => 'Level',
            'logo'       => 'Logo',
            'newsletter' => 'Newsletter',
            'product'    => 'Product',
            'record'     => 'Record',
            'setting'    => 'Setting',
            'sensor'     => 'Sensor',
            'stock'      => 'Stock',
            'store'      => 'Store',
            'site'       => 'Site',
            'target'     => 'Target',
            'variable'   => 'Variable'
        ];
    }
}

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
    /**
     * @param String  $type
     * @return Array
     */
    public static function getCodes(string $type): array
    {
        $items = [];
        $types = self::all();

        switch ($type) {
            case "relation":
                foreach ($types as $key => $value)
                    array_push($items, $key);
                break;
            case "class":
                foreach ($types as $value)
                    array_push($items, $value);
                break;
        }

        return $items;
    }

    /**
     * @param Bool  $onlyVaild
     * @return Array
     */
    public static function options($onlyVaild = false): array
    {
        $items = $onlyVaild ? [] : ['' => trans('php-core::system.null')];

        $types = self::all();
        foreach ($types as $key => $value) {
            $items = array_merge($items, [$key => trans('php-morph-category::system.morphType.'.$key)]);
        }

        return $items;
    }

    /**
     * @return Array
     */
    public static function all(): array
    {
        return [
            'api'        => 'API',
            'article'    => 'Article',
            'blog'       => 'Blog',
            'catalog'    => 'Catalog',
            'category'   => 'Category',
            'card'       => 'Card',
            'device'     => 'Device',
            'friendship' => 'Friendship',
            'group'      => 'Group',
            'icon'       => 'Icon',
            'image'      => 'Image',
            'product'    => 'Product',
            'record'     => 'Record',
            'setting'    => 'Setting',
            'sensor'     => 'Sensor',
            'stock'      => 'Stock',
            'store'      => 'Store',
            'site'       => 'Site',
            'variable'   => 'Variable'
        ];
    }
}

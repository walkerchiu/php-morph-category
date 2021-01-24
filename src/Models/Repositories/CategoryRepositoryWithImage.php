<?php

namespace WalkerChiu\MorphCategory\Models\Repositories;

use WalkerChiu\MorphCategory\Models\Repositories\CategoryRepository;
use WalkerChiu\MorphImage\Models\Repositories\ImageRepositoryTrait;

class CategoryRepositoryWithImage extends CategoryRepository
{
    use ImageRepositoryTrait;

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @param Array   $data
     * @param Int     $page
     * @param Int     $nums per page
     * @param Boolean $is_enabled
     * @param String  $target
     * @param Boolean $target_is_enabled
     * @param Array   $exceptData
     * @return Array
     */
    public function list($host_type, $host_id, String $code, Array $data, $page = null, $nums = null, $is_enabled = null, $target = null, $target_is_enabled = null, $exceptData = [])
    {
        $this->assertForPagination($page, $nums);

        if (empty($host_type) || empty($host_id)) {
            $entity = $this->entity;
        } else {
            $entity = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        if ($is_enabled === true)      $entity = $entity->ofEnabled();
        elseif ($is_enabled === false) $entity = $entity->ofDisabled();

        $data = array_map('trim', $data);
        $records = $entity->with(['langs' => function ($query) use ($code) {
                                $query->ofCurrent()
                                      ->ofCode($code);
                             }])
                            ->when( config('wk-morph-category.onoff.morph-tag') && !empty(config('wk-core.class.morph-tag.tag')), function ($query) {
                                return $query->with(['tags', 'tags.langs']);
                            })
                            ->when($exceptData, function ($query, $exceptData) {
                                return $query->where(function($query) use ($exceptData) {
                                    return $query->whereNull('type')
                                                 ->orWhereNotIn('type', $exceptData);
                                });
                            })
                            ->when($data, function ($query, $data) {
                                return $query->when(empty($data['id']), function ($query) use ($data) {
                                                return $query->whereNull('ref_id');
                                            }, function ($query) use ($data) {
                                                return $query->where('ref_id', $data['id']);
                                            })
                                        ->unless(empty($data['type']), function ($query) use ($data) {
                                            return $query->where('type', $data['type']);
                                        })
                                        ->unless(empty($data['attribute_set']), function ($query) use ($data) {
                                            return $query->where('attribute_set', $data['attribute_set']);
                                        })
                                        ->unless(empty($data['serial']), function ($query) use ($data) {
                                            return $query->where('serial', $data['serial']);
                                        })
                                        ->unless(empty($data['identifier']), function ($query) use ($data) {
                                            return $query->where('identifier', $data['identifier']);
                                        })
                                        ->unless(empty($data['order']), function ($query) use ($data) {
                                            return $query->where('order', $data['order']);
                                        })
                                        ->unless(empty($data['name']), function ($query) use ($data) {
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'name')
                                                      ->where('value', 'LIKE', "%".$data['name']."%");
                                            });
                                        })
                                        ->unless(empty($data['description']), function ($query) use ($data) {
                                            return $query->whereHas('langs', function($query) use ($data) {
                                                $query->ofCurrent()
                                                      ->where('key', 'description')
                                                      ->where('value', 'LIKE', "%".$data['description']."%");
                                            });
                                        });
                              })
                            ->orderBy('order', 'ASC')
                            ->get()
                            ->when(is_integer($page) && is_integer($nums), function ($query) use ($page, $nums) {
                                return $query->forPage($page, $nums);
                            });
        $list = [];
        foreach ($records as $record) {
            $this->setEntity($record);

            $data = $record->toArray();
            array_push($list,
                array_merge($data, [
                    'name'           => $record->findLangByKey('name'),
                    'description'    => $record->findLangByKey('description'),
                    'covers'         => $this->getlistOfCovers($code),
                    'icons'          => $this->getlistOfIcons($code),
                    'child_disabled' => $record->categories(null, 0)->count(),
                    'child_enabled'  => $record->categories(null, 1)->count()
                ])
            );
        }

        return $list;
    }

    /**
     * @param String  $host_type
     * @param Int     $host_id
     * @param String  $code
     * @param String  $code_default
     * @param String  $type
     * @param Int     $id
     * @param Int     $degree
     * @param String  $target
     * @param Boolean $target_is_enabled
     * @return Array
     */
    public function listMenu($host_type, $host_id, String $code, String $code_default, $type = null, $id = null, $degree = 0, $target = null, $target_is_enabled = null)
    {
        if (!is_integer($degree) || $degree < 0) throw new NotUnsignedIntegerException($degree);

        if (empty($host_type) || empty($host_id)) {
            $entity = $this->entity;
        } else {
            $entity = $this->baseQueryForRepository($host_type, $host_id, $target, $target_is_enabled);
        }
        $records = $entity->with(['langs' => function ($query) use ($code) {
                                  $query->ofCurrent();
                              }])
                          ->ofEnabled()
                          ->when(is_null($type), function ($query) {
                                  return $query->whereNull('type');
                              }, function ($query) use ($type) {
                                  return $query->where('type', $type);
                              })
                          ->when(empty($id), function ($query) {
                                  return $query->whereNull('ref_id');
                              }, function ($query) use ($id) {
                                  return $query->where('ref_id', $id);
                              })
                          ->orderBy('order', 'ASC')
                          ->select('id', 'attribute_set', 'serial', 'identifier', 'url', 'target', 'icon', 'order')
                          ->get();
        $list = [];
        foreach ($records as $record) {
            $name        = $record->findLang($code, 'name');
            $description = $record->findLang($code, 'description');
            if (empty($name)) {
                $name        = $record->findLang($code_default, 'name');
                $description = $record->findLang($code_default, 'description');
                if (empty($name))
                    continue;
            }
            $data = [
                'id'            => $record->id,
                'attribute_set' => $record->attribute_set,
                'serial'        => $record->serial,
                'identifier'    => $record->identifier,
                'url'           => $record->url,
                'target'        => $record->target,
                'icon'          => $record->icon,
                'order'         => $record->order,
                'images'        => $record->images,
                'name'          => $name,
                'description'   => $description,
                'covers'        => $this->getlistOfCovers($code),
                'icons'         => $this->getlistOfIcons($code)
            ];
            if ($degree > 0)
                $data['child'] = $this->listMenu($host_type, $host_id, $code, $code_default, $type, $record->id, $degree-1, $target, $target_is_enabled);

            array_push($list, $data);
        }

        return $list;
    }

    /**
     * @param Category $entity
     * @param String   $code
     * @param String   $code_default
     * @return Array
     */
    public function listBreadcrumb($id, String $code, $code_default = null, $data = [])
    {
        $entity = $this->find($id);

        if (is_null($code_default))
            $code_default = config('wk-core.language');

        if ($entity->reference) {
            $data = $this->listBreadcrumb($entity->reference->id, $code, $code_default, $data);
        }

        $name        = $entity->findLang($code, 'name');
        $description = $entity->findLang($code, 'description');
        if (empty($name)) {
            $name        = $entity->findLang($code_default, 'name');
            $description = $entity->findLang($code_default, 'description');
        }

        array_push($data, [
            'id'            => $entity->id,
            'attribute_set' => $entity->attribute_set,
            'identifier'    => $entity->identifier,
            'url'           => $entity->url,
            'icon'          => $entity->icon,
            'images'        => $entity->images,
            'name'          => $name,
            'description'   => $description,
            'icons'         => $this->getlistOfIcons($code)
        ]);

        return $data;
    }

    /**
     * @param Category     $entity
     * @param String|Array $code
     * @param String       $code_default
     * @return Array
     */
    public function show($entity, $code, $code_default = null)
    {
        $data = [
            'id' => $entity ? $entity->id : '',
            'basic' => [],
            'icons' => []
        ];

        if (empty($entity))
            return $data;

        $this->setEntity($entity);

        if (is_string($code)) {
            $data['basic'] = [
                  'host_type'     => $entity->host_type,
                  'host_id'       => $entity->host_id,
                  'type'          => $entity->type,
                  'attribute_set' => $entity->attribute_set,
                  'ref_id'        => $entity->ref_id,
                  'ref_name'      => $entity->ref_id ? $entity->reference->findLang($code, 'name') : '',
                  'serial'        => $entity->serial,
                  'identifier'    => $entity->identifier,
                  'url'           => $entity->url,
                  'order'         => $entity->order,
                  'images'        => $entity->images,
                  'name'          => $entity->findLang($code, 'name'),
                  'description'   => $entity->findLang($code, 'description'),
                  'target'        => $entity->target,
                  'icon'          => $entity->icon,
                  'is_enabled'    => $entity->is_enabled,
                  'updated_at'    => $entity->updated_at,
                  'breadcrumb'    => $this->listBreadcrumb($entity->id, $code, $code_default)
            ];

        } elseif (is_array($code)) {
            foreach ($code as $language) {
                $data['basic'][$language] = [
                      'host_type'     => $entity->host_type,
                      'host_id'       => $entity->host_id,
                      'type'          => $entity->type,
                      'attribute_set' => $entity->attribute_set,
                      'ref_id'        => $entity->ref_id,
                      'ref_name'      => $entity->ref_id ? $entity->reference->findLang($language, 'name') : '',
                      'serial'        => $entity->serial,
                      'identifier'    => $entity->identifier,
                      'url'           => $entity->url,
                      'order'         => $entity->order,
                      'images'        => $entity->images,
                      'name'          => $entity->findLang($language, 'name'),
                      'description'   => $entity->findLang($language, 'description'),
                      'target'        => $entity->target,
                      'icon'          => $entity->icon,
                      'is_enabled'    => $entity->is_enabled,
                      'updated_at'    => $entity->updated_at,
                      'breadcrumb'    => $this->listBreadcrumb($entity->id, $language, $code_default)
                ];
            }
        }
        $data['covers'] = $this->getlistOfCovers($code);
        $data['icons']  = $this->getlistOfIcons($code);

        return $data;
    }
}

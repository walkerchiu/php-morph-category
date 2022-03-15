<?php

namespace WalkerChiu\MorphCategory\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class CategoryService
{
    use CheckExistTrait;

    protected $repository;



    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->repository = App::make(config('wk-core.class.morph-category.categoryRepository'));
    }

    /**
     * Insert default category
     *
     * @param Array  $data_basic
     * @param Array  $data_lang
     * @return Category
     */
    public function insertDefaultCategory(array $data_basic, array $data_lang)
    {
        $category = $this->repository->save($data_basic);

        foreach ($data_lang as $lang) {
            $lang['morph_type'] = get_class($category);
            $lang['morph_id']   = $category->id;
            $this->repository->createLangWithoutCheck($lang);
        }

        return $category;
    }

    /**
     * @param String  $host_type
     * @param String  $host_id
     * @param String  $code
     * @param String  $code_default
     * @param String  $type
     * @param String  $id
     * @param Int     $degree
     * @return Array
     */
    public function listOption(?string $host_type, ?string $host_id, string $code, string $code_default, ?string $type, ?string $id, $degree = 0): array
    {
        return $this->repository->listOption($host_type, $host_id, $code, $code_default, $type, $id, $degree);
    }

    /**
     * @param Category  $category
     * @param String    $code
     * @param Bool      $include
     * @return Array
     */
    public function loadCategoryPath($category, $code = null, $include = false): array
    {
        if (empty($category))
            return [];
        if (empty($code))
            $code = config('app.locale');

        $path = [];
        while (true) {
            if ($include) {
                array_push($path, [
                    'id'   => $category->id,
                    'name' => $category->findLang($code, 'name')
                ]);
                if (empty($category->ref_id))
                    break;
                $category = $category->parent();
            } else {
                if (empty($category->ref_id))
                    break;
                $category = $category->parent();
                array_push($path, [
                    'id'   => $category->id,
                    'name' => $category->findLang($code, 'name')
                ]);
            }
        }
        return array_reverse($path);
    }

    /**
     * @param Category  $category
     * @param String    $code
     * @return String
     */
    public function loadCategoryPathText($category, $code = null): string
    {
        if (empty($category))
            return '';
        if (empty($code))
            $code = config('app.locale');

        $path = [$category->findLang($code, 'name') . ' / '];
        while (true) {
            if (empty($category->ref_id))
                break;
            $category = $category->parent();
            $name = $category->findLang($code, 'name') . ' / ';
            array_push($path, $name);
        }
        return implode('', array_reverse($path));
    }

    /**
     * @param Bool      $isOwner
     * @param Category  $record
     * @param String    $code
     * @param Bool      $transform
     * @return Array
     */
    public function loadParentOptions(bool $isOwner, $record, $code = null, $transform = true): array
    {
        if (empty($code))
            $code = config('app.locale');

        $parent = $record->parent();
        if (is_a($parent, config('wk-core.class.blog.blog'))) {
            $categories = $isOwner
                            ? $parent->categories()->whereNull('ref_id')->get()
                            : $parent->categories(null, true)->whereNull('ref_id')->get();
        } else {
            $categories = $isOwner
                            ? $parent->categories()->get()
                            : $parent->categories(null, true)->get();
        }

        $result = [];
        foreach ($categories as $category) {
            if ($transform)
                array_push($result, [
                    'value' => $category->id,
                    'label' => $category->findLang($code, 'name')
                ]);
            else
                array_push($result, [
                    'id'   => $category->id,
                    'name' => $category->findLang($code, 'name')
                ]);
        }

        return $result;
    }

    /**
     * @param Bool      $isOwner
     * @param Blog      $blog
     * @param Category  $record
     * @param String    $code
     * @param Bool      $transform
     * @return Array
     */
    public function loadCategoryOptions(bool $isOwner, $blog, $record = null, $code = null, $transform = true): array
    {
        $result = [];
        $categories = $isOwner
                        ? $blog->categories()->get()
                        : $blog->categories(null, true)->get();

        foreach ($categories as $category) {
            if (
                $record
                && (
                    $category->id == $record->id
                    || $this->checkIsChildren($category, $record->id)
                )
            )
                continue;

            if ($transform)
                array_push($result, [
                    'value' => $category->id,
                    'label' => $this->loadCategoryPathText($category, $code)
                ]);
            else
                array_push($result, [
                    'id'   => $category->id,
                    'name' => $this->loadCategoryPathText($category, $code)
                ]);
        }

        return $result;
    }

    /**
     * @param Category  $object
     * @param String    $id
     * @return Bool
     */
    private function checkIsChildren($object, string $id): bool
    {
        while (is_a($object, config('wk-core.class.morph-category.category'))) {
            $object = $object->parent();

            if ($object->ref_id == $id)
                return true;
        }

        return false;
    }
}

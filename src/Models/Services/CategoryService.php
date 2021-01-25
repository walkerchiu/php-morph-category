<?php

namespace WalkerChiu\MorphCategory\Models\Services;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Services\CheckExistTrait;

class CategoryService
{
    use CheckExistTrait;

    protected $repository;

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
    public function insertDefaultCategory($data_basic, $data_lang)
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
     * @param String $host_type
     * @param String $host_id
     * @param String $code
     * @param String $code_default
     * @param String $type
     * @param String $id
     * @param Int    $degree
     * @return Array
     */
    public function listOption($host_type, $host_id, String $code, String $code_default, $type = null, $id = null, $degree = 0)
    {
        return $this->repository->listOption($host_type, $host_id, $code, $code_default, $type, $id, $degree);
    }
}

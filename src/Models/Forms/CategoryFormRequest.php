<?php

namespace WalkerChiu\MorphCategory\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class CategoryFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (string) $request->id;
            $this->getInputSource()->replace($data);
        }

        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'host_type'     => trans('php-morph-category::system.host_type'),
            'host_id'       => trans('php-morph-category::system.host_id'),
            'ref_id'        => trans('php-morph-category::system.ref_id'),
            'type'          => trans('php-morph-category::system.type'),
            'attribute_set' => trans('php-morph-category::system.attribute_set'),
            'serial'        => trans('php-morph-category::system.serial'),
            'identifier'    => trans('php-morph-category::system.identifier'),
            'url'           => trans('php-morph-category::system.url'),
            'target'        => trans('php-morph-category::system.target'),
            'cover'         => trans('php-morph-category::system.cover'),
            'order'         => trans('php-morph-category::system.order'),
            'images'        => trans('php-morph-category::system.images'),
            'is_enabled'    => trans('php-morph-category::system.is_enabled'),

            'name'          => trans('php-morph-category::system.name'),
            'description'   => trans('php-morph-category::system.description')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'host_type'     => 'required_with:host_id|string',
            'host_id'       => 'required_with:host_type|string',
            'ref_id'        => 'nullable|string',
            'type'          => '',
            'attribute_set' => '',
            'serial'        => '',
            'identifier'    => 'required|string|max:255',
            'url'           => '',
            'target'        => '',
            'cover'         => 'nullable|string|min:2',
            'order'         => 'nullable|numeric|min:0',
            'images'        => 'nullable|json',
            'is_enabled'    => 'required|boolean',

            'name'          => 'required|string|max:255',
            'description'   => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.morph-category.categories').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'             => trans('php-core::validation.required'),
            'id.string'               => trans('php-core::validation.string'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.string'          => trans('php-core::validation.string'),
            'ref_id.string'           => trans('php-core::validation.string'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.max'          => trans('php-core::validation.max'),
            'cover.string'            => trans('php-core::validation.string'),
            'cover.min'               => trans('php-core::validation.min'),
            'order.numeric'           => trans('php-core::validation.numeric'),
            'order.min'               => trans('php-core::validation.min'),
            'images.json'             => trans('php-core::validation.json'),
            'is_enabled.required'     => trans('php-core::validation.required'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required'           => trans('php-core::validation.required'),
            'name.string'             => trans('php-core::validation.string'),
            'name.max'                => trans('php-core::validation.max')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();
            if (
                isset($data['host_type'])
                && isset($data['host_id'])
            ) {
                if (
                    config('wk-morph-category.onoff.site')
                    && !empty(config('wk-core.class.site.site'))
                    && $data['host_type'] == config('wk-core.class.site.site')
                ) {
                    $result = DB::table(config('wk-core.table.site.sites'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                } elseif (
                    config('wk-morph-category.onoff.group')
                    && !empty(config('wk-core.class.group.group'))
                    && $data['host_type'] == config('wk-core.class.group.group')
                ) {
                    $result = DB::table(config('wk-core.table.group.groups'))
                                ->where('id', $data['host_id'])
                                ->exists();
                    if (!$result)
                        $validator->errors()->add('host_id', trans('php-core::validation.exists'));
                }
            }
            if (
                isset($data['identifier'])
                && !in_array($data['identifier'], ['#', '/'])
            ) {
                $result = config('wk-core.class.morph-category.category')::where('identifier', $data['identifier'])
                                ->when(isset($data['host_type']), function ($query) use ($data) {
                                    return $query->where('host_type', $data['host_type']);
                                  })
                                ->when(isset($data['host_id']), function ($query) use ($data) {
                                    return $query->where('host_id', $data['host_id']);
                                  })
                                ->when(isset($data['id']), function ($query) use ($data) {
                                    return $query->where('id', '<>', $data['id']);
                                  })
                                ->exists();
                if ($result)
                    $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-morph-category::system.identifier')]));
            }
        });
    }
}

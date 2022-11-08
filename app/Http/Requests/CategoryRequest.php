<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->route('category')) {
            return Gate::allows('categories.update');
        }
        return Gate::allows('categories.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('category');
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('categories', 'name')->ignore($id),

                ////Ways for Validation 
                // (1) build a clouser function :
                // function($attribute,$value, $fails){
                //     if(strtolower($value) == 'laravel') {
                //         $fails('thi name is not correct to enter');
                //     }
                // }

                // (2) Call class created form Rules (GLOBAL RULE)
                //  new Filter(['php', 'laravel','html'])

                // (3) from AppServiceProvider (GLOBAL RULE) can Write our validation Facdes in boot method and call it
                'filter:php,laravel,html',
            ],
            'parent_id' => [
                'nullable', 'int', 'exists:categories,id'
            ],
            'image' => [
                'image', 'max:1048576', 'dimensions:min_width=100,min_height=100',
            ],
            'status' => 'required|in:active,archived',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'this filed (:attribute) is required',

            'name.unique' => 'this name is already exists'

        ];
    }
}

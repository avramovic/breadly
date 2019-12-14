<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class TinyWebDbStoreAValueRequest
 * @package App\Http\Requests
 *
 * @property string $tag
 * @property string $value
 */

class TinyWebDbStoreAValueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tag'   => 'required|string',
            'value' => 'required|string',
        ];
    }
}

<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class PostStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'unique:posts'],
            'text' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->slug === null) {
            $this->slug = Str::random(9);
        }

        $this->merge([
            'slug' => Str::slug($this->slug),
            'user_id' => auth('api')->id(),
        ]);
    }

    public function getData(): array
    {
        return $this->only([
           'slug',
           'text',
           'user_id',
        ]);
    }
}

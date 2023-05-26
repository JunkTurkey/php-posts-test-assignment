<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->post);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'nullable',
                'string',
                Rule::unique('posts')->ignore($this->post->id),
            ],
            'text' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->slug) {
            return;
        }

        $this->merge([
            'slug' => Str::slug($this->slug),
        ]);
    }

    public function getData(): array
    {
        $data = [
            'text' => $this->text,
        ];

        if ($this->slug) {
            $data['slug'] = $this->slug;
        }

        return $data;
    }
}

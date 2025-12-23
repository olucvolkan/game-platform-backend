<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class GameListRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:1',
            'perPage' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
            'sort' => 'sometimes|string|in:popularity,price-asc,price-desc,newest,discount',
            'minPrice' => 'sometimes|numeric|min:0',
            'maxPrice' => 'sometimes|numeric|min:0',
            'types' => 'sometimes|string|max:500',
            'platforms' => 'sometimes|string|max:500',
            'genres' => 'sometimes|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be a positive integer.',
            'page.min' => 'Page must be at least 1.',
            'perPage.integer' => 'Items per page must be a positive integer.',
            'perPage.max' => 'Items per page cannot exceed 100.',
            'sort.in' => 'Sort must be one of: popularity, price-asc, price-desc, newest, discount.',
            'minPrice.numeric' => 'Minimum price must be a number.',
            'maxPrice.numeric' => 'Maximum price must be a number.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults
        $this->merge([
            'page' => $this->input('page', 1),
            'perPage' => $this->input('perPage', 20),
            'sort' => $this->input('sort', 'popularity'),
        ]);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  Validator  $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'error' => 'Invalid request parameters',
            'statusCode' => 400,
            'details' => $validator->errors(),
        ], 400));
    }
}

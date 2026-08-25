<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkReviewArticlesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_revisor;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'articles' => ['required', 'array', 'min:1', 'max:100'],
            'articles.*' => ['required', 'integer', 'distinct', Rule::exists('articles', 'id')],
            'decision' => ['required', Rule::in(['accept', 'reject'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'articles.required' => __('ui.select_at_least_one_article'),
            'articles.min' => __('ui.select_at_least_one_article'),
            'articles.*.exists' => __('ui.selected_article_unavailable'),
            'decision.required' => __('ui.choose_review_decision'),
        ];
    }
}

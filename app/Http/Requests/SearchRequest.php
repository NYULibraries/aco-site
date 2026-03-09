<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'scope' => 'in:matches,containsAny,containsAll',
            'page' => 'integer|min:1',
            'title' => 'sometimes|nullable|string',
            'author' => 'sometimes|nullable|string',
            'category' => 'sometimes|nullable|string',
            'publisher' => 'sometimes|nullable|string',
            'pubplace' => 'sometimes|nullable|string',
            'provider' => 'sometimes|nullable|string',
            'subject' => 'sometimes|nullable|string',
            'q' => 'sometimes|nullable|string',
            'q1' => 'sometimes|nullable|string',
            'rpp' => 'sometimes|nullable|integer|min:1|max:100',
            'sort' => 'sometimes|nullable|string',
        ];
    }

    /**
     * Get the search data in array format.
     *
     * @return array
     */
    public function getSearchData(): ?array
    {
        return collect(['q', 'q1', 'title', 'author', 'category', 'publisher', 'pubplace', 'provider', 'subject'])
            ->filter(fn($field) => $this->filled($field))
            ->map(fn($field) => [
                'field' => $field === 'q1' ? 'q' : $field,  // Transform q1 to q
                'value' => $this->input($field),
            ])
            ->values()
            ->first();
    }

    /**
     * Get the rows per request.
     *
     * @return int
     */
    public function getRows(): int
    {
        return (int) $this->input('rpp', 10);
    }

    /**
     * Get the sort.
     *
     * @return string
     */
    public function getSort(): string
    {

        return $this->input('sort', 'score desc');

    }

    /**
     * Get the sort.
     *
     * @return string
     */
    public function getSortDir(): string
    {
        $sort = $this->getSort();

        $decoded = urldecode($sort);

        $explode = explode(' ', $decoded);

        return $explode[1];

    }

    /**
     * Get the sort.
     *
     * @return string
     */
    public function getSortField(): string
    {

        $sort = $this->getSort();

        $decoded = urldecode($sort);

        $explode = explode(' ', $decoded);

        return $explode[0];

    }

    /**
     * Get the page number.
     *
     * @return int
     */
    public function getPage(): int
    {
        return (int) $this->input('page', 1);
    }

    /**
     * Get the search scope.
     *
     * @return string
     */
    public function getScope(): string
    {
        return $this->input('scope', 'containsAny');
    }
}

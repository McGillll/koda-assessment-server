<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\ModelFilters\ProjectFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectIndexRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => ['bail', 'nullable', 'string', Rule::enum(ProjectStatus::class)],
            'priority' => ['bail', 'nullable', 'string', Rule::enum(ProjectPriority::class)],
            'search' => ['bail', 'nullable', 'string', 'max:255'],
            'sort_by' => ['bail', 'nullable', 'string', Rule::in(ProjectFilter::SORTABLE)],
            'sort_dir' => ['bail', 'nullable', 'string', Rule::in(['asc', 'desc'])],
            'page' => ['bail', 'nullable', 'integer', 'min:1'],
            'per_page' => ['bail', 'nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages()
    {
        $statusMessage = 'Status must be one of: '.implode(', ', ProjectStatus::values()).'.';
        $priorityMessage = 'Priority must be one of: '.implode(', ', ProjectPriority::values()).'.';
        $sortByMessage = 'Sort field must be one of: '.implode(', ', ProjectFilter::SORTABLE).'.';
        $sortDirMessage = 'Sort direction must be asc or desc.';

        return [
            'status.string' => $statusMessage,
            'status.enum' => $statusMessage,
            'priority.string' => $priorityMessage,
            'priority.enum' => $priorityMessage,
            'search.string' => 'Search must be text.',
            'search.max' => 'Search must not exceed 255 characters.',
            'sort_by.string' => $sortByMessage,
            'sort_by.in' => $sortByMessage,
            'sort_dir.string' => $sortDirMessage,
            'sort_dir.in' => $sortDirMessage,
            'page.integer' => 'Page must be a whole number of 1 or more.',
            'page.min' => 'Page must be a whole number of 1 or more.',
            'per_page.integer' => 'Per page must be a whole number between 1 and 100.',
            'per_page.min' => 'Per page must be a whole number between 1 and 100.',
            'per_page.max' => 'Per page must be a whole number between 1 and 100.',
        ];
    }
}

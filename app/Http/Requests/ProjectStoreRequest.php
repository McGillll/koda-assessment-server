<?php

namespace App\Http\Requests;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProjectStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_name' => ['bail', 'required', 'string', 'max:255'],
            'project_name' => ['bail', 'required', 'string', 'max:255'],
            'description' => ['bail', 'nullable', 'string', 'max:5000'],
            'status' => ['bail', 'required', 'string', Rule::enum(ProjectStatus::class)],
            'priority' => ['bail', 'required', 'string', Rule::enum(ProjectPriority::class)],
            'start_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
            'due_date' => ['bail', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['start_date', 'due_date'])) {
                    return;
                }

                $startDate = $this->input('start_date');
                $dueDate = $this->input('due_date');

                if ($startDate && $dueDate && $dueDate < $startDate) {
                    $validator->errors()->add('due_date', 'Due date cannot be earlier than the start date.');
                }
            },
        ];
    }

    public function messages()
    {
        $statusMessage = 'Status must be one of: '.implode(', ', ProjectStatus::values()).'.';
        $priorityMessage = 'Priority must be one of: '.implode(', ', ProjectPriority::values()).'.';

        return [
            'client_name.required' => 'Client name is required.',
            'client_name.string' => 'Client name must be text.',
            'client_name.max' => 'Client name must not exceed 255 characters.',
            'project_name.required' => 'Project name is required.',
            'project_name.string' => 'Project name must be text.',
            'project_name.max' => 'Project name must not exceed 255 characters.',
            'description.string' => 'Description must be text.',
            'description.max' => 'Description must not exceed 5000 characters.',
            'status.required' => 'Status is required.',
            'status.string' => $statusMessage,
            'status.enum' => $statusMessage,
            'priority.required' => 'Priority is required.',
            'priority.string' => $priorityMessage,
            'priority.enum' => $priorityMessage,
            'start_date.date_format' => 'Start date must be a valid date in YYYY-MM-DD format.',
            'due_date.date_format' => 'Due date must be a valid date in YYYY-MM-DD format.',
        ];
    }
}

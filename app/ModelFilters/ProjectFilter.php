<?php

namespace App\ModelFilters;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use EloquentFilter\ModelFilter;

class ProjectFilter extends ModelFilter
{
    public const SORTABLE = ['client_name', 'project_name', 'status', 'priority', 'start_date', 'due_date', 'created_at'];

    private const NULLABLE_SORTS = ['start_date', 'due_date'];

    private const RANKED_SORTS = [
        'status' => ProjectStatus::class,
        'priority' => ProjectPriority::class,
    ];

    public function setup()
    {
        if (! $this->input('sort_by')) {
            $this->sortBy('due_date');
        }
    }

    public function status($payload)
    {
        return $this->where('status', $payload);
    }

    public function priority($payload)
    {
        return $this->where('priority', $payload);
    }

    public function search($payload)
    {
        return $this->where(function ($query) use ($payload) {
            $query->where('client_name', 'like', "%{$payload}%")
                ->orWhere('project_name', 'like', "%{$payload}%");
        });
    }

    public function sortBy($payload)
    {
        $direction = $this->input('sort_dir') === 'desc' ? 'desc' : 'asc';

        $this->applySort($payload, $direction);

        if ($payload !== 'due_date') {
            $this->applySort('due_date', 'asc');
        }

        return $this;
    }

    private function applySort($column, $direction)
    {
        if (isset(self::RANKED_SORTS[$column])) {
            $values = array_map(fn ($case) => $case->value, self::RANKED_SORTS[$column]::cases());
            $whens = implode(' ', array_map(fn ($rank) => "WHEN ? THEN {$rank}", array_keys($values)));

            $this->orderByRaw("CASE {$column} {$whens} END {$direction}", $values);

            return;
        }

        if (in_array($column, self::NULLABLE_SORTS, true)) {
            $this->orderByRaw("{$column} IS NULL");
        }

        $this->orderBy($column, $direction);
    }
}

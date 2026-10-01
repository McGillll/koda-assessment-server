<?php

namespace App\Models;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\ModelFilters\ProjectFilter;
use App\Traits\UsesUuid;
use Database\Factories\ProjectFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use Filterable, HasFactory, SoftDeletes, UsesUuid;

    protected function casts()
    {
        return [
            'status' => ProjectStatus::class,
            'priority' => ProjectPriority::class,
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function modelFilter()
    {
        return $this->provideFilter(ProjectFilter::class);
    }
}

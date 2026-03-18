<?php

namespace App\Observers;

use App\Models\Department;
use Illuminate\Support\Facades\Cache;

class DepartmentObserver
{
    /**
     * Reset the department badge count cache on create or update.
     */
    public function saved(Department $department): void
    {
        Cache::forget('departments-badge-count');
    }

    /**
     * Reset the department badge count cache on deletion.
     */
    public function deleted(Department $department): void
    {
        Cache::forget('departments-badge-count');
    }
}

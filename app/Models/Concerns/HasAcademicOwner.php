<?php

namespace App\Models\Concerns;

use App\Models\Admin;

trait HasAcademicOwner
{
    public function canBeManagedBy(Admin $user): bool
    {
        return $user->is_admin && (in_array($user->adminRole(), ['admin', 'super_admin'], true)
            || ($user->adminRole() === 'exam_manager' && $this->created_by !== null
                && (int) $this->created_by === (int) $user->id));
    }
}

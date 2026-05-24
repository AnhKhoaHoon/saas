<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\ProjectPermission;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Kiểm tra quyền xem project bằng mapping Spatie theo role trong team_members.
        return app(ProjectPermission::class)->userCan($user, $project, 'projects.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // Kiểm tra quyền cập nhật project bằng permission projects.update.
        return app(ProjectPermission::class)->userCan($user, $project, 'projects.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Kiểm tra quyền xóa project bằng permission projects.delete.
        return app(ProjectPermission::class)->userCan($user, $project, 'projects.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}

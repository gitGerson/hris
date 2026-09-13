<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JobLevel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class JobLevelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JobLevel');
    }

    public function view(AuthUser $authUser, JobLevel $jobLevel): bool
    {
        return $authUser->can('View:JobLevel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JobLevel');
    }

    public function update(AuthUser $authUser, JobLevel $jobLevel): bool
    {
        return $authUser->can('Update:JobLevel');
    }

    public function delete(AuthUser $authUser, JobLevel $jobLevel): bool
    {
        return $authUser->can('Delete:JobLevel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:JobLevel');
    }

    public function restore(AuthUser $authUser, JobLevel $jobLevel): bool
    {
        return $authUser->can('Restore:JobLevel');
    }

    public function forceDelete(AuthUser $authUser, JobLevel $jobLevel): bool
    {
        return $authUser->can('ForceDelete:JobLevel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JobLevel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JobLevel');
    }

    public function replicate(AuthUser $authUser, JobLevel $jobLevel): bool
    {
        return $authUser->can('Replicate:JobLevel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JobLevel');
    }
}

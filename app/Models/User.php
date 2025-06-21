<?php

namespace App\Models;

use App\Models\Traits\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, RecordsActivity;

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected static array $recordableEvents = ['created', 'updated', 'deleted'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
        'eselon_2_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override the recordActivity method from trait for User-specific logging.
     * User activities are not tied to a project, so project_id will be null.
     */
    public function recordActivity($description)
    {
        $this->activity()->create([
            'user_id' => auth()->id(), // User who performed the action
            'description' => $description,
            'project_id' => null, // No project associated with user management
            'before' => $this->getActivityChanges('before'),
            'after' => $this->getActivityChanges('after')
        ]);
    }

    /**
     * Override the activityOwner method because User model does not have a 'project' relationship.
     * The owner of the activity is the authenticated user.
     */
    protected function activityOwner()
    {
        return auth()->user() ?? $this;
    }

    //======================================================================
    // HIERARCHY & RELATIONSHIPS
    //======================================================================

    /**
     * Get the direct superior of this user.
     */
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the direct subordinates of this user.
     */
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Get all projects led by this user.
     */
    public function ledProjects()
    {
        return $this->hasMany(Project::class, 'leader_id');
    }

    /**
     * Get all projects this user is a member of.
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id');
    }

    /**
     * Get all tasks assigned to this user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to_id');
    }

    /**
     * Get all time logs created by this user.
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    //======================================================================
    // HELPER METHODS
    //======================================================================
    
    /**
     * Recursively get all subordinate IDs under this user.
     */
    public function getAllSubordinateIds(): array
    {
        $subordinateIds = [];
        $children = $this->children()->with('children')->get(); // Eager load for efficiency

        foreach ($children as $child) {
            $subordinateIds[] = $child->id;
            // Merge with the subordinate IDs of the child
            $subordinateIds = array_merge($subordinateIds, $child->getAllSubordinateIds());
        }

        return $subordinateIds;
    }

    /**
     * Check if this user is a subordinate of another user.
     */
    public function isSubordinateOf(User $potentialSuperior): bool
    {
        $current = $this;
        while ($current->parent) {
            if ($current->parent->id === $potentialSuperior->id) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
    }

    /**
     * Check if the user has a top-level management role.
     */
    public function isTopLevelManager(): bool
    {
        return in_array($this->role, ['superadmin', 'Eselon I', 'Eselon II']);
    }

    /**
     * Check if the user has authority to manage other users.
     */
    public function canManageUsers(): bool
    {
        return in_array($this->role, ['superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }

    /**
     * Check if the user has authority to create projects.
     */
    public function canCreateProjects(): bool
    {
        return in_array($this->role, ['superadmin', 'Eselon I', 'Eselon II', 'Koordinator']);
    }
}
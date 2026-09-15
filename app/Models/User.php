<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // Compatibility model for older student integrations. Admins use Admin explicitly.
    protected $table = 'students';

    public const ADMIN_ROLES = [
        'admin' => 'Admin',
        'exam_manager' => 'Exam Manager',
        'content_editor' => 'Content Editor',
    ];

    public function adminRole(): string
    {
        return $this->is_super_admin ? 'super_admin' : ($this->admin_role ?? 'admin');
    }

    public function canAccessAdminRoute(string $route): bool
    {
        if (! $this->is_admin) {
            return false;
        }
        if (in_array($route, ['admin.logout', 'admin.profile.show', 'admin.profile.update', 'admin.profile.password.update'], true)) {
            return true;
        }
        if (str_starts_with($route, 'admin.users.')) {
            return (bool) $this->is_super_admin;
        }
        if (in_array($this->adminRole(), ['super_admin', 'admin'], true)) {
            return true;
        }
        $patterns = match ($this->adminRole()) {
            'exam_manager' => ['admin.index', 'admin.questions.*', 'questions.*', 'admin.exams.*', 'exams.*', 'admin.academic.*', 'admin.classes.*', 'admin.subjects.*', 'admin.chapters.*', 'admin.results.*', 'admin.custom-results.*'],
            'content_editor' => ['admin.blogs.*'],
            default => [],
        };

        return \Illuminate\Support\Str::is($patterns, $route);
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'school_id',
        'academic_class_id',
        'profile_photo_path',
        'password',
        'is_admin',
        'is_super_admin',
        'admin_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'user_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class);
    }
}

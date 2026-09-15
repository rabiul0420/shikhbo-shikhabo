<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $adminReferences = [
        'exams' => 'created_by', 'questions' => 'created_by',
        'academic_classes' => 'created_by', 'subjects' => 'created_by',
        'chapters' => 'created_by', 'gift_awards' => 'given_by',
    ];

    public function up(): void
    {
        // Validate before any DDL: never silently discard a legacy owner.
        foreach ($this->adminReferences as $table => $column) {
            if (DB::table($table)->join('users', 'users.id', '=', "$table.$column")
                ->where('users.is_admin', false)->exists()) {
                throw new RuntimeException("$table.$column contains a student owner; resolve ownership before migrating.");
            }
        }

        foreach (['admins' => true, 'students' => false] as $name => $isAdmin) {
            Schema::create($name, function (Blueprint $table) use ($isAdmin) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
                $table->boolean('is_admin')->default($isAdmin);
                $table->boolean('is_super_admin')->default(false);
                $table->string('admin_role', 30)->nullable();
                $table->string('phone', 30)->nullable()->unique();
                $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('academic_class_id')->nullable()->constrained()->nullOnDelete();
                $table->string('profile_photo_path')->nullable();
            });

            $columns = Schema::getColumnListing($name);
            DB::table('users')->where('is_admin', $isAdmin)->orderBy('id')->chunkById(500, function ($users) use ($name, $columns) {
                DB::table($name)->insert($users->map(fn ($user) => array_intersect_key((array) $user, array_flip($columns)))->all());
            });
        }

        foreach ($this->adminReferences as $table => $column) {
            $this->retarget($table, $column, 'admins', 'set null');
        }

        foreach (['exam_attempts', 'custom_exams', 'custom_exam_attempts'] as $table) {
            // Preserve historical admin participation without assigning it to a student
            // whose independent ID may later overlap with the admin's ID.
            $this->dropReference($table, 'user_id');
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
                $blueprint->unsignedBigInteger('user_id')->nullable()->change();
            });
            DB::table($table)->whereIn('user_id', DB::table('admins')->select('id'))
                ->update(['admin_id' => DB::raw('user_id'), 'user_id' => null]);
            $this->retarget($table, 'user_id', 'students', $table === 'exam_attempts' ? 'set null' : 'cascade');
        }

        // Read-only recovery snapshot; no authentication provider uses this table.
        Schema::rename('users', 'users_legacy');
    }

    private function retarget(string $table, string $column, string $target, string $onDelete): void
    {
        $this->dropReference($table, $column);
        Schema::table($table, fn (Blueprint $blueprint) => $blueprint->foreign($column)
            ->references('id')->on($target)->onDelete($onDelete));
    }

    private function dropReference(string $table, string $column): void
    {
        foreach (Schema::getForeignKeys($table) as $foreign) {
            if ($foreign['columns'] === [$column]) {
                $key = DB::getDriverName() === 'sqlite' ? [$column] : $foreign['name'];
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropForeign($key));
            }
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Account separation requires restoring the pre-migration backup; independent account IDs cannot be safely merged automatically.');
    }
};

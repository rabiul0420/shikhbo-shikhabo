<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccountMigrationTest extends TestCase
{
    public function test_existing_ids_hashes_ownership_and_student_and_admin_results_are_preserved(): void
    {
        $original = DB::getDefaultConnection();
        config(['database.connections.account_migration_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]]);
        DB::setDefaultConnection('account_migration_test');
        try {
            foreach (glob(database_path('migrations/*.php')) as $file) {
                if (! str_contains($file, 'separate_admin_and_student_accounts')) {
                    (require $file)->up();
                }
            }
            $hash = password_hash('existing-password', PASSWORD_BCRYPT);
            DB::table('users')->insert([
                ['id' => 7, 'name' => 'Existing admin', 'email' => 'admin@example.test', 'password' => $hash, 'is_admin' => true],
                ['id' => 9, 'name' => 'Existing student', 'email' => 'student@example.test', 'password' => $hash, 'is_admin' => false],
            ]);
            DB::table('academic_classes')->insert(['id' => 1, 'name' => 'Class 5']);
            DB::table('subjects')->insert(['id' => 1, 'name' => 'Math']);
            DB::table('chapters')->insert(['id' => 1, 'name' => 'Numbers', 'academic_class_id' => 1, 'subject_id' => 1]);
            DB::table('exams')->insert(['id' => 4, 'title' => 'Existing exam', 'slug' => 'existing-exam', 'created_by' => 7, 'academic_class_id' => 1, 'subject_id' => 1, 'chapter_id' => 1]);
            DB::table('exam_attempts')->insert([
                ['id' => 1, 'exam_id' => 4, 'user_id' => 7, 'score' => 3],
                ['id' => 2, 'exam_id' => 4, 'user_id' => 9, 'score' => 5],
            ]);
            (require database_path('migrations/2026_09_14_000000_separate_admin_and_student_accounts.php'))->up();
            $this->assertSame($hash, DB::table('admins')->where('id', 7)->value('password'));
            $this->assertSame($hash, DB::table('students')->where('id', 9)->value('password'));
            $this->assertSame(1, DB::table('admins')->count());
            $this->assertSame(1, DB::table('students')->count());
            $this->assertSame(7, DB::table('exams')->value('created_by'));
            $this->assertSame(7, DB::table('exam_attempts')->where('id', 1)->value('admin_id'));
            $this->assertNull(DB::table('exam_attempts')->where('id', 1)->value('user_id'));
            $this->assertSame(9, DB::table('exam_attempts')->where('id', 2)->value('user_id'));
            $this->assertSame(5, DB::table('exam_attempts')->where('id', 2)->value('score'));
            $this->assertSame(2, DB::table('users_legacy')->count());
            $this->assertFalse(Schema::hasTable('users'));
            $this->assertSame([], DB::select('PRAGMA foreign_key_check'));
        } finally {
            DB::setDefaultConnection($original);
            DB::purge('account_migration_test');
        }
    }
}

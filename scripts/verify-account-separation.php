<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    foreach (['admins' => true, 'students' => false] as $table => $isAdmin) {
        $columns = Schema::getColumnListing($table);
        $old = DB::table('users_legacy')->where('is_admin', $isAdmin)->orderBy('id')->get($columns)->toJson();
        $new = DB::table($table)->orderBy('id')->get($columns)->toJson();
        if ($old !== $new) {
            throw new RuntimeException('Copied account data differs: '.$table);
        }
        echo $table.': '.DB::table($table)->count()." accounts; all copied fields match.\n";
    }
    foreach (['exams', 'questions', 'academic_classes', 'subjects', 'chapters', 'gift_awards', 'exam_attempts', 'custom_exams', 'custom_exam_attempts'] as $table) {
        foreach (Schema::getForeignKeys($table) as $key) {
            if (in_array($key['foreign_table'], ['users', 'users_legacy'], true)) {
                throw new RuntimeException('Legacy foreign key remains: '.$table);
            }
        }
    }
    $adminAttempts = DB::table('exam_attempts')->whereNotNull('admin_id')->count();
    if (DB::table('exam_attempts')->whereNotNull('admin_id')->whereNotNull('user_id')->exists()) {
        throw new RuntimeException('An attempt is assigned to both account types.');
    }
    echo "Historical admin attempts preserved: $adminAttempts\n";
    echo "Account migration verification passed.\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
}

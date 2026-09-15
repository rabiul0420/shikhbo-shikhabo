<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_records_owner_and_only_owner_can_update_and_delete(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $other = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $this->actingAs($manager)->post(route('admin.classes.store'), ['name' => 'Owned class', 'created_by' => $other->id])
            ->assertSessionHas('status', 'Class added.');
        $class = AcademicClass::where('name', 'Owned class')->firstOrFail();
        $this->post(route('admin.subjects.store'), ['name' => 'Owned subject', 'academic_class_ids' => [$class->id], 'created_by' => $other->id])
            ->assertSessionHas('status', 'Subject added.');
        $subject = Subject::where('name', 'Owned subject')->firstOrFail();
        $chapterData = ['name' => 'Owned chapter', 'chapter_no' => '1', 'academic_class_id' => $class->id, 'subject_id' => $subject->id];
        $this->post(route('admin.chapters.store'), $chapterData + ['created_by' => $other->id])
            ->assertSessionHas('status', 'Oddhay / Chapter added.');
        $chapter = Chapter::where('name', 'Owned chapter')->firstOrFail();

        foreach (['classes' => $class, 'subjects' => $subject, 'chapters' => $chapter] as $type => $record) {
            $this->assertEquals($manager->id, $record->created_by);
            $this->actingAs($other)->patch(route("admin.$type.update", $record), ['name' => 'Stolen', 'created_by' => $other->id])->assertForbidden();
            $this->delete(route("admin.$type.destroy", $record))->assertForbidden();
            $this->get(route("admin.academic.$type"))->assertOk()->assertSee($record->name)
                ->assertDontSee(route("admin.$type.update", $record))->assertDontSee(route("admin.$type.destroy", $record));
        }

        $this->actingAs($manager)->patch(route('admin.classes.update', $class), ['name' => 'Updated class', 'created_by' => $other->id])->assertSessionHas('status', 'Class updated.');
        $this->patch(route('admin.subjects.update', $subject), ['name' => 'Updated subject', 'academic_class_ids' => [$class->id], 'created_by' => $other->id])->assertSessionHas('status', 'Subject updated.');
        $this->patch(route('admin.chapters.update', $chapter), array_merge($chapterData, ['name' => 'Updated chapter', 'created_by' => $other->id]))->assertSessionHas('status', 'Oddhay / Chapter updated.');
        foreach (['chapters' => $chapter, 'subjects' => $subject, 'classes' => $class] as $type => $record) {
            $this->assertEquals($manager->id, $record->fresh()->created_by);
            $this->delete(route("admin.$type.destroy", $record))->assertRedirect();
            $this->assertModelMissing($record);
        }
    }

    public function test_legacy_records_are_admin_only_and_parent_delete_cannot_remove_others_work(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $admin = Admin::factory()->create(['is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Shared class', 'created_by' => $manager->id]);
        $subject = Subject::create(['name' => 'Shared subject', 'created_by' => $manager->id]);
        $chapter = Chapter::create(['name' => 'Legacy chapter', 'academic_class_id' => $class->id, 'subject_id' => $subject->id]);
        $this->actingAs($manager)->delete(route('admin.chapters.destroy', $chapter))->assertForbidden();
        $this->patch(route('admin.chapters.update', $chapter), [])->assertForbidden();
        $this->delete(route('admin.classes.destroy', $class))->assertForbidden();
        $this->delete(route('admin.subjects.destroy', $subject))->assertForbidden();
        $chapter->update(['created_by' => $manager->id]);
        $exam = Exam::create(['title' => 'Admin exam', 'created_by' => $admin->id, 'academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id]);
        foreach (['classes' => $class, 'subjects' => $subject, 'chapters' => $chapter] as $type => $record) {
            $this->delete(route("admin.$type.destroy", $record))->assertForbidden();
        }
        $this->assertModelExists($exam);
        $legacy = AcademicClass::create(['name' => 'Legacy class']);
        $this->patch(route('admin.classes.update', $legacy), ['name' => 'Changed'])->assertForbidden();
        $this->actingAs($admin)->patch(route('admin.classes.update', $legacy), ['name' => 'Admin updated'])->assertSessionHas('status', 'Class updated.');
        $super = Admin::factory()->create(['is_admin' => true, 'is_super_admin' => true, 'admin_role' => 'exam_manager']);
        $this->actingAs($super)->delete(route('admin.classes.destroy', $legacy))->assertRedirect();
        $this->assertModelMissing($legacy);
    }
}

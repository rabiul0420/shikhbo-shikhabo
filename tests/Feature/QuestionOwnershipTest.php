<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_and_bulk_creation_record_authenticated_owner_and_do_not_claim_duplicates(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $other = Admin::factory()->create(['is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Class 5']);
        $subject = Subject::create(['name' => 'Math']);
        $subject->academicClasses()->attach($class);
        $chapter = Chapter::create(['name' => 'Numbers', 'academic_class_id' => $class->id, 'subject_id' => $subject->id]);
        $data = ['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id, 'created_by' => $other->id];
        $this->actingAs($manager)->post(route('questions.standalone.store'), $data + [
            'question_text' => 'Single question', 'options' => ['One', 'Two'], 'correct_option' => 1,
        ])->assertSessionHas('status', 'Question added.');
        $single = Question::where('question_text', 'Single question')->firstOrFail();
        $this->assertEquals($manager->id, $single->created_by);
        $this->post(route('questions.bulk.store'), $data + [
            'bulk_questions' => "1. Bulk question\nA. One\nB. Two\nAnswer: B",
        ])->assertSessionHas('status', '1 questions added.');
        $bulk = Question::where('question_text', 'Bulk question')->firstOrFail();
        $this->assertEquals($manager->id, $bulk->created_by);
        $this->actingAs($other)->post(route('questions.bulk.store'), $data + [
            'bulk_questions' => "1. Bulk question\nA. One\nB. Two\nAnswer: B",
        ])->assertSessionHas('status', '0 questions added. 1 skipped.');
        $this->assertEquals($manager->id, $bulk->fresh()->created_by);
    }

    public function test_only_owner_can_change_questions_and_legacy_questions_remain_admin_only(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $other = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $own = Question::create(['question_text' => 'Owned question', 'created_by' => $manager->id, 'type' => 'single_choice', 'marks' => 1]);
        $foreign = Question::create(['question_text' => 'Foreign question', 'created_by' => $other->id, 'type' => 'single_choice', 'marks' => 1]);
        $legacy = Question::create(['question_text' => 'Legacy question', 'type' => 'single_choice', 'marks' => 1]);
        $payload = ['question_text' => 'Updated question', 'options' => ['Yes', 'No'], 'correct_option' => 0, 'created_by' => $other->id];
        $this->actingAs($manager)->get(route('admin.index'))->assertOk()
            ->assertSee('Foreign question')->assertSee(route('questions.update', $own))
            ->assertDontSee(route('questions.update', $foreign))->assertDontSee(route('questions.destroy', $foreign))
            ->assertDontSee(route('questions.update', $legacy));
        foreach ([$foreign, $legacy] as $question) {
            $this->patch(route('questions.update', $question), $payload)->assertForbidden();
            $this->delete(route('questions.destroy', $question))->assertForbidden();
            $this->assertModelExists($question);
        }
        $this->patch(route('questions.update', $own), $payload)->assertSessionHas('status', 'Question updated.');
        $this->assertEquals($manager->id, $own->fresh()->created_by);
        $this->assertSame('Updated question', $own->fresh()->question_text);
        $this->assertSame(2, $own->options()->count());
        $this->delete(route('questions.destroy', $own))->assertSessionHas('status', 'Question deleted.');
        $this->assertModelMissing($own);
        $admin = Admin::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->patch(route('questions.update', $legacy), $payload)->assertSessionHas('status', 'Question updated.');
        $super = Admin::factory()->create(['is_admin' => true, 'is_super_admin' => true, 'admin_role' => 'exam_manager']);
        $this->actingAs($super)->delete(route('questions.destroy', $foreign))->assertSessionHas('status', 'Question deleted.');
    }

    public function test_manager_cannot_delete_others_questions_by_deleting_their_parent(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $class = AcademicClass::create(['name' => 'Class 5', 'created_by' => $manager->id]);
        $subject = Subject::create(['name' => 'Math', 'created_by' => $manager->id]);
        $chapter = Chapter::create(['name' => 'Numbers', 'academic_class_id' => $class->id, 'subject_id' => $subject->id, 'created_by' => $manager->id]);
        $question = Question::create(['question_text' => 'Legacy question', 'academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id, 'type' => 'single_choice', 'marks' => 1]);
        $this->actingAs($manager);
        foreach (['classes' => $class, 'subjects' => $subject, 'chapters' => $chapter] as $type => $record) {
            $this->delete(route("admin.$type.destroy", $record))->assertForbidden();
        }
        $this->assertModelExists($question);
    }
}

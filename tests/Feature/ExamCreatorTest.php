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

class ExamCreatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_filter_separates_running_upcoming_and_expired_exams(): void
    {
        $admin = Admin::factory()->create(['is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Class 6']);
        $subject = Subject::create(['name' => 'Math']);
        $chapter = Chapter::create(['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'name' => 'Numbers']);
        $attributes = ['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id, 'created_by' => $admin->id];
        foreach ([null, today()->subDays(5), today(), today()->addDay()] as $index => $startsAt) {
            Exam::create($attributes + ['title' => 'Exam '.$index, 'starts_at' => $startsAt, 'ends_at' => $index === 1 ? today()->subDay() : ($index === 0 ? null : today()->addDays(5))]);
        }
        Exam::where('title', 'Exam 2')->update(['ends_at' => today()]);
        $this->actingAs($admin);
        foreach (['running' => ['Exam 0', 'Exam 2'], 'upcoming' => ['Exam 3'], 'expired' => ['Exam 1']] as $status => $expected) {
            $response = $this->getJson(route('admin.exams.data', ['status' => $status, 'class_id' => $class->id, 'creator_id' => $admin->id]))
                ->assertOk()->assertJsonPath('recordsTotal', 4)->assertJsonPath('recordsFiltered', count($expected));
            $this->assertEquals($expected, collect($response->json('data'))->pluck('title')->sort()->values()->all());
        }
        $this->getJson(route('admin.exams.data'))->assertOk()->assertJsonPath('recordsFiltered', 4);
    }

    public function test_exam_manager_can_list_all_exams_but_only_modify_owned_exams(): void
    {
        $manager = Admin::factory()->create(['is_admin' => true, 'admin_role' => 'exam_manager']);
        $other = Admin::factory()->create(['is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Class 5']);
        $subject = Subject::create(['name' => 'Math']);
        $subject->academicClasses()->attach($class);
        $chapter = Chapter::create(['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'name' => 'Numbers']);
        $attributes = ['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id];
        $own = Exam::create($attributes + ['title' => 'My exam', 'created_by' => $manager->id]);
        $foreign = Exam::create($attributes + ['title' => 'Other exam', 'created_by' => $other->id]);
        $unknown = Exam::create($attributes + ['title' => 'Unassigned exam']);
        $question = \App\Models\Question::create($attributes + ['question_text' => 'One plus one?', 'type' => 'mcq', 'marks' => 1]);
        $payload = $attributes + [
            'title' => 'Updated exam', 'starts_at' => '2026-09-13', 'ends_at' => '2026-09-14',
            'duration_minutes' => 30, 'question_ids' => [$question->id], 'created_by' => $other->id,
        ];

        $this->actingAs($manager)->get(route('admin.exams.index'))->assertOk()
            ->assertViewHas('examCount', 3)
            ->assertViewHas('creators', fn ($creators) => $creators->pluck('id')->sort()->values()->all() === [$manager->id, $other->id])
            ->assertDontSee(route('admin.exams.results', $foreign));
        $this->getJson(route('admin.exams.data'))->assertOk()
            ->assertJsonPath('recordsTotal', 3)->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.title', 'My exam')
            ->assertJsonPath('data.1.title', 'Other exam')
            ->assertJsonPath('data.2.title', 'Unassigned exam');
        foreach ([['creator_id' => $other->id], ['search' => ['value' => 'Other']]] as $filter) {
            $response = $this->getJson(route('admin.exams.data', $filter))->assertOk()
                ->assertJsonPath('recordsTotal', 3)->assertJsonPath('recordsFiltered', 1)->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.title', 'Other exam');
            $this->assertStringContainsString('View only', $response->json('data.0.actions'));
            $this->assertStringNotContainsString('js-edit-exam', $response->json('data.0.actions'));
            $this->assertStringNotContainsString('Delete', $response->json('data.0.actions'));
        }
        foreach ([$foreign, $unknown] as $exam) {
            $this->getJson(route('admin.exams.edit-data', $exam))->assertForbidden();
            $this->patch(route('exams.update', $exam), $payload)->assertForbidden();
            $this->delete(route('exams.destroy', $exam))->assertForbidden();
            $this->get(route('admin.exams.results', $exam))->assertForbidden();
        }
        $this->assertSame('Other exam', $foreign->fresh()->title);
        $this->getJson(route('admin.exams.edit-data', $own))->assertOk();
        $this->patch(route('exams.update', $own), $payload)->assertSessionHas('status', 'Exam updated.');
        $this->assertSame('Updated exam', $own->fresh()->title);
        $this->assertEquals($manager->id, $own->fresh()->created_by);
        $this->assertEquals([$question->id], $own->questions()->pluck('questions.id')->all());

        $student = Student::factory()->create();
        $ownAttempt = \App\Models\ExamAttempt::create(['exam_id' => $own->id, 'user_id' => $student->id, 'score' => 1, 'total_marks' => 1, 'submitted_at' => now()]);
        $foreignAttempt = \App\Models\ExamAttempt::create(['exam_id' => $foreign->id, 'user_id' => $student->id, 'score' => 1, 'total_marks' => 1, 'submitted_at' => now()]);
        $this->get(route('admin.results.index'))->assertOk()
            ->assertViewHas('examAttempts', fn ($attempts) => $attempts->modelKeys() === [$ownAttempt->id]);
        $this->get(route('admin.results.show', $foreignAttempt))->assertForbidden();
        $this->get(route('admin.exams.results', $foreign))->assertForbidden();
        $this->get(route('admin.exams.results', $own->fresh()))->assertOk();

        $this->actingAs($other)->getJson(route('admin.exams.data'))->assertJsonPath('recordsTotal', 3);
        $this->getJson(route('admin.exams.edit-data', $own->fresh()))->assertOk();
        $super = Admin::factory()->create(['is_admin' => true, 'is_super_admin' => true, 'admin_role' => 'exam_manager']);
        $this->actingAs($super)->getJson(route('admin.exams.data'))->assertJsonPath('recordsTotal', 3);
    }

    public function test_admin_can_identify_search_filter_and_sort_exam_creators(): void
    {
        $admin = Admin::factory()->create(['name' => 'Zara', 'is_admin' => true]);
        $creator = Admin::factory()->create(['name' => 'Amina <Teacher>', 'is_admin' => true, 'admin_role' => 'exam_manager']);
        $class = AcademicClass::create(['name' => 'Class 5']);
        $subject = Subject::create(['name' => 'Math']);
        $chapter = Chapter::create(['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'name' => 'Numbers']);
        foreach ([$admin->id, $creator->id, null] as $index => $creatorId) {
            Exam::create([
                'title' => 'Exam '.$index, 'created_by' => $creatorId,
                'academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id,
            ]);
        }

        $this->actingAs($admin)->get(route('admin.exams.index'))->assertOk()->assertSee('Created by')->assertSee('All creators');
        $this->getJson(route('admin.exams.data', ['creator_id' => $creator->id]))
            ->assertOk()->assertJsonPath('recordsTotal', 3)->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.title', 'Exam 1')->assertJsonPath('data.0.creator', 'Amina &lt;Teacher&gt;');
        $this->getJson(route('admin.exams.data', ['search' => ['value' => 'Amina']]))
            ->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.title', 'Exam 1');
        $this->getJson(route('admin.exams.data', ['order' => [['column' => 8, 'dir' => 'desc']]]))
            ->assertOk()->assertJsonPath('data.0.creator', 'Zara')->assertJsonPath('data.2.creator', 'Unknown');
        $this->actingAs($creator)->getJson(route('admin.exams.data'))->assertOk();
        $student = Student::factory()->create(['is_admin' => false]);
        $this->actingAs($student)->getJson(route('admin.exams.data'))->assertForbidden();
    }
}

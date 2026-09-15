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

class QuestionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_filters_combine_with_search_and_persist_during_pagination(): void
    {
        $manager = Admin::factory()->create(['name' => 'Manager A', 'is_admin' => true, 'admin_role' => 'exam_manager']);
        $other = Admin::factory()->create(['name' => 'Manager B', 'is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Class 5']);
        $subject = Subject::create(['name' => 'Math']);
        $subject->academicClasses()->attach($class);
        $chapter = Chapter::create(['name' => 'Numbers', 'academic_class_id' => $class->id, 'subject_id' => $subject->id]);
        $base = ['academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id, 'type' => 'single_choice', 'marks' => 1];
        for ($i = 0; $i < 26; $i++) {
            Question::create($base + ['question_text' => "Match $i", 'created_by' => $manager->id]);
        }
        $foreign = Question::create($base + ['question_text' => 'Match foreign', 'created_by' => $other->id]);
        $filters = ['class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id, 'creator_id' => $manager->id, 'question_search' => 'Match'];
        $this->actingAs($manager)->get(route('admin.index', $filters))->assertOk()
            ->assertViewHas('questions', function ($questions) use ($filters) {
                parse_str(parse_url($questions->nextPageUrl(), PHP_URL_QUERY), $query);
                $this->assertEquals($filters, array_diff_key($query, ['page' => true]));
                return $questions->total() === 26 && $questions->count() === 25;
            })->assertSee('Clear')->assertSee('All creators')->assertDontSee('Match foreign');
        $this->get(route('admin.index', $filters + ['page' => 2]))->assertOk()
            ->assertViewHas('questions', fn ($questions) => $questions->count() === 1 && $questions->total() === 26);
        foreach (['class_id', 'subject_id', 'chapter_id', 'creator_id'] as $key) {
            $this->get(route('admin.index', array_merge($filters, [$key => 999999])))->assertOk()
                ->assertViewHas('questions', fn ($questions) => $questions->total() === 0)
                ->assertSee('No questions matched your search or filters.');
        }
        $this->get(route('admin.index', ['creator_id' => $other->id]))->assertOk()
            ->assertViewHas('questions', fn ($questions) => $questions->total() === 1)
            ->assertSee('Match foreign')->assertDontSee(route('questions.update', $foreign));
        $this->get(route('admin.index', ['question_search' => 'Manager B']))->assertOk()
            ->assertViewHas('questions', fn ($questions) => $questions->total() === 1);
        $this->get(route('admin.index'))->assertOk()
            ->assertViewHas('questions', fn ($questions) => $questions->total() === 27);
    }
}

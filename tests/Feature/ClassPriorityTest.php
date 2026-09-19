<?php

namespace Tests\Feature;

use App\Http\Controllers\ClassExamController;
use App\Models\AcademicClass;
use App\Models\Admin;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_priority_can_be_saved_and_controls_frontend_order(): void
    {
        $admin = Admin::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post(route('admin.classes.store'), ['name' => 'A Class', 'priority' => 20])
            ->assertSessionHas('status', 'Class added.');
        $first = AcademicClass::where('name', 'A Class')->firstOrFail();
        $second = AcademicClass::create(['name' => 'Z Class', 'priority' => 10]);
        $subject = Subject::create(['name' => 'Math']);
        foreach ([$first, $second] as $class) {
            $chapter = Chapter::create(['name' => 'Chapter 1', 'academic_class_id' => $class->id, 'subject_id' => $subject->id]);
            Exam::create(['title' => $class->name.' exam', 'academic_class_id' => $class->id, 'subject_id' => $subject->id, 'chapter_id' => $chapter->id, 'created_by' => $admin->id]);
        }

        $this->assertEquals([$second->id, $first->id], app(ClassExamController::class)->directory()->getData()['classes']->pluck('id')->all());
        $this->get('/')->assertOk()->assertViewHas('examsByClass', fn ($groups) => $groups->keys()->all() === [$second->id, $first->id])
            ->assertSeeInOrder(['Z Class exam', 'A Class exam']);
        $this->get('/bn')->assertOk()->assertViewHas('examsByClass', fn ($groups) => $groups->keys()->all() === [$second->id, $first->id]);

        $this->patch(route('admin.classes.update', $first), ['name' => $first->name, 'priority' => 5])
            ->assertSessionHas('status', 'Class updated.');
        $this->assertSame(5, $first->fresh()->priority);
        $this->assertEquals([$first->id, $second->id], app(ClassExamController::class)->directory()->getData()['classes']->pluck('id')->all());
        $this->get('/')->assertOk()->assertViewHas('examsByClass', fn ($groups) => $groups->keys()->all() === [$first->id, $second->id])
            ->assertSeeInOrder(['A Class exam', 'Z Class exam']);

        $this->patch(route('admin.classes.update', $first), ['name' => $first->name, 'priority' => -1])
            ->assertSessionHasErrors('priority');
        $this->assertSame(5, $first->fresh()->priority);
        $this->get(route('admin.academic.classes'))->assertOk()->assertSee('<th>Priority</th>', false);
    }
}

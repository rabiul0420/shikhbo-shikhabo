<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Admin;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassSubjectUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_class_subjects_can_be_replaced_and_cleared_without_changing_other_classes(): void
    {
        $admin = Admin::factory()->create(['is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Class 5']);
        $otherClass = AcademicClass::create(['name' => 'Class 6']);
        $math = Subject::create(['name' => 'Math']);
        $english = Subject::create(['name' => 'English']);
        $class->subjects()->attach($math);
        $otherClass->subjects()->attach($math);

        $this->actingAs($admin)->get(route('admin.academic.classes'))
            ->assertOk()->assertSee('name="subject_ids[]"', false)->assertSee('English');

        $this->patch(route('admin.classes.update', $class), [
            'name' => $class->name, 'update_subjects' => 1, 'subject_ids' => [$english->id],
        ])->assertSessionHas('status', 'Class updated.');
        $this->assertEquals([$english->id], $class->subjects()->pluck('subjects.id')->all());
        $this->assertEquals([$math->id], $otherClass->subjects()->pluck('subjects.id')->all());

        $this->patch(route('admin.classes.update', $class), ['name' => 'Renamed class'])
            ->assertSessionHas('status', 'Class updated.');
        $this->assertEquals([$english->id], $class->subjects()->pluck('subjects.id')->all());

        $this->patch(route('admin.classes.update', $class), [
            'name' => 'Renamed class', 'update_subjects' => 1,
        ])->assertSessionHas('status', 'Class updated.');
        $this->assertCount(0, $class->subjects()->get());
    }

    public function test_invalid_subjects_do_not_change_the_class(): void
    {
        $admin = Admin::factory()->create(['is_admin' => true]);
        $class = AcademicClass::create(['name' => 'Class 5']);
        $subject = Subject::create(['name' => 'Math']);
        $class->subjects()->attach($subject);

        $this->actingAs($admin)->patch(route('admin.classes.update', $class), [
            'name' => 'Changed', 'update_subjects' => 1, 'subject_ids' => [999999],
        ])->assertSessionHasErrors('subject_ids.0');
        $this->assertEquals('Class 5', $class->fresh()->name);
        $this->assertEquals([$subject->id], $class->subjects()->pluck('subjects.id')->all());
    }
}

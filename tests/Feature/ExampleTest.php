<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_top_menu_shows_public_pages_and_login_only(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('Contact Us')
            ->assertSee('Privacy Policy')
            ->assertSee('Login')
            ->assertDontSee('Take Exam')
            ->assertDontSee('My Profile')
            ->assertDontSee('My Result')
            ->assertDontSee('Logout')
            ->assertDontSee('Register');
    }

    public function test_logged_in_top_menu_shows_profile_and_logout(): void
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get('/')
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('Contact Us')
            ->assertSee('Privacy Policy')
            ->assertSee('My Profile')
            ->assertSee('My Result')
            ->assertSee('Logout')
            ->assertDontSee('Take Exam')
            ->assertDontSee('Login')
            ->assertDontSee('Register');
    }

    public function test_profile_page_requires_login_and_shows_account_details(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'phone' => '01712345678',
            'academic_class_id' => $class->id,
            'school_id' => \App\Models\School::create([
                'title' => 'Student School',
                'address' => 'Dhaka',
                'status' => 'active',
            ])->id,
            'is_admin' => false,
        ]);

        $this->get('/my-profile')->assertRedirect('/login');

        $this->actingAs($student)
            ->get('/my-profile')
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Student User')
            ->assertSee('student@example.com')
            ->assertSee('Student')
            ->assertSee('Class 5');
    }

    public function test_student_can_view_own_results_page(): void
    {
        $student = User::factory()->create(['is_admin' => false]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $student->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Bangla Practice Exam',
        ]);

        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->get('/my-results')->assertRedirect('/login');

        $this->actingAs($student)
            ->get('/my-results')
            ->assertOk()
            ->assertSee('My Result')
            ->assertSee('Bangla Practice Exam')
            ->assertSee('8 / 10')
            ->assertSee('80% score')
            ->assertSee('View details')
            ->assertSee('All result')
            ->assertSee(route('exams.results', $exam));
    }

    public function test_admin_can_open_all_results_from_my_results_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Bangla Practice Exam',
        ]);

        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $admin->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/my-results')
            ->assertOk()
            ->assertSee('All result')
            ->assertSee(route('exams.results', $exam));
    }

    public function test_student_can_view_all_results_for_same_exam(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create(['name' => 'Current Student', 'academic_class_id' => $class->id, 'is_admin' => false]);
        $otherStudent = User::factory()->create(['name' => 'Other Student', 'academic_class_id' => $class->id, 'is_admin' => false]);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $student->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Bangla Practice Exam',
        ]);
        $ownAttempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        $otherAttempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $otherStudent->id,
            'status' => 'graded',
            'score' => 6,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('exams.results', $exam))
            ->assertOk()
            ->assertSee('All Result')
            ->assertSee('Current Student')
            ->assertSee('Other Student')
            ->assertSee('Highest Mark')
            ->assertSee('My Position')
            ->assertSee('#1')
            ->assertSee('8 / 10')
            ->assertSee('6 / 10')
            ->assertSee(route('exam-attempts.result', $ownAttempt))
            ->assertDontSee(route('exam-attempts.result', $otherAttempt))
            ->assertSee('Details private');
    }

    public function test_public_info_pages_are_available(): void
    {
        $this->get('/about-us')->assertOk()->assertSee('About Us');
        $this->get('/contact-us')->assertOk()->assertSee('Contact Us');
        $this->get('/privacy-policy')->assertOk()->assertSee('Privacy Policy');
    }

    public function test_logged_in_student_sees_only_own_class_exams(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $classFive = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $classSix = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $student = User::factory()->create([
            'academic_class_id' => $classFive->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapterFive = \App\Models\Chapter::create([
            'academic_class_id' => $classFive->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $chapterSix = \App\Models\Chapter::create([
            'academic_class_id' => $classSix->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);

        \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $classFive->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapterFive->id,
            'title' => 'Class Five Exam',
        ]);
        \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $classSix->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapterSix->id,
            'title' => 'Class Six Exam',
        ]);

        $this->actingAs($student)
            ->get('/')
            ->assertOk()
            ->assertSee('Class Five Exam')
            ->assertSee('Exams for Class 5 are shown here.')
            ->assertDontSee('Class Six Exam');
    }

    public function test_student_cannot_open_another_class_exam_directly(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $classFive = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $classSix = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $student = User::factory()->create([
            'academic_class_id' => $classFive->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapterSix = \App\Models\Chapter::create([
            'academic_class_id' => $classSix->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $classSix->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapterSix->id,
            'title' => 'Class Six Exam',
        ]);

        $this->actingAs($student)
            ->get(route('exams.show', $exam))
            ->assertForbidden();
    }

    public function test_student_can_register_with_phone_number_and_school(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $school = \App\Models\School::create([
            'title' => 'Registration School',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);

        $this->get('/register')
            ->assertOk()
            ->assertSee('Phone Number')
            ->assertSee('Class')
            ->assertSee('Class 5')
            ->assertSee('School')
            ->assertSee('Registration School');

        $this->post('/register', [
            'name' => 'New Student',
            'email' => 'new-student@example.com',
            'phone' => '01711111111',
            'academic_class_id' => $class->id,
            'school_name' => 'Registration School',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Account created.');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'New Student',
            'email' => 'new-student@example.com',
            'phone' => '01711111111',
            'school_id' => $school->id,
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
    }

    public function test_student_registration_creates_pending_school_when_school_is_not_listed(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 6']);

        $this->post('/register', [
            'name' => 'Pending School Student',
            'email' => 'pending-school-student@example.com',
            'phone' => '01811111111',
            'academic_class_id' => $class->id,
            'school_name' => 'New Pending School',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Account created.');

        $this->assertDatabaseHas('schools', [
            'title' => 'New Pending School',
            'address' => 'Added during student registration',
            'status' => 'pending',
        ]);

        $school = \App\Models\School::where('title', 'New Pending School')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'name' => 'Pending School Student',
            'email' => 'pending-school-student@example.com',
            'phone' => '01811111111',
            'school_id' => $school->id,
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_student_cannot_access_admin_panel(): void
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Schools')
            ->assertSee(route('admin.schools.index'))
            ->assertSee('Student List')
            ->assertSee(route('admin.students.index'));
    }

    public function test_super_admin_can_see_user_menu_and_manage_admin_users(): void
    {
        $superAdmin = User::factory()->create([
            'email' => 'super-admin@example.com',
            'is_admin' => true,
            'is_super_admin' => true,
            'name' => 'Super Admin',
        ]);
        $admin = User::factory()->create([
            'email' => 'regular-admin@example.com',
            'is_admin' => true,
            'is_super_admin' => false,
            'name' => 'Regular Admin',
        ]);
        $student = User::factory()->create([
            'email' => 'student-user@example.com',
            'is_admin' => false,
            'name' => 'Student User',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSee('User')
            ->assertSee('User List')
            ->assertSee('Add User')
            ->assertSee(route('admin.users.index'))
            ->assertSee(route('admin.users.create'));

        $this->actingAs($superAdmin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Admin User List')
            ->assertSee('Super Admin')
            ->assertSee('regular-admin@example.com')
            ->assertDontSee('student-user@example.com');

        $this->actingAs($superAdmin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Add Admin User');

        $this->actingAs($superAdmin)
            ->post(route('admin.users.store'), [
                'name' => 'New Admin',
                'email' => 'new-admin@example.com',
                'phone' => '01722222222',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Admin user added.');

        $this->assertDatabaseHas('users', [
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'phone' => '01722222222',
            'is_admin' => true,
            'is_super_admin' => false,
        ]);
    }

    public function test_normal_admin_cannot_see_or_access_user_management(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_super_admin' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertDontSee(route('admin.users.index'))
            ->assertDontSee(route('admin.users.create'));

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Blocked Admin',
                'email' => 'blocked-admin@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_view_student_list(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
            'name' => 'Admin User',
        ]);
        $student = User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'phone' => '01712345678',
            'academic_class_id' => $class->id,
            'school_id' => \App\Models\School::create([
                'title' => 'Student School',
                'address' => 'Dhaka',
                'status' => 'active',
            ])->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Bangla Practice Exam',
        ]);

        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Student List')
            ->assertSee('Student User')
            ->assertSee('student@example.com')
            ->assertSee('01712345678')
            ->assertSee('Class 5')
            ->assertSee('Student School')
            ->assertSee('>1<', false)
            ->assertDontSee('admin@example.com');
    }

    public function test_student_cannot_access_student_list(): void
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get(route('admin.students.index'))
            ->assertForbidden();
    }

    public function test_admin_can_add_and_view_schools(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.schools.index'))
            ->assertOk()
            ->assertSee('Schools')
            ->assertSee('School List')
            ->assertSee('Add School');

        $this->actingAs($admin)
            ->post(route('admin.schools.store'), [
                'title' => 'Green Valley School',
                'address' => '12 Lake Road, Dhaka',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.schools.index'))
            ->assertSessionHas('status', 'School added.');

        $this->assertDatabaseHas('schools', [
            'title' => 'Green Valley School',
            'address' => '12 Lake Road, Dhaka',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.schools.index'))
            ->assertOk()
            ->assertSee('Green Valley School')
            ->assertSee('12 Lake Road, Dhaka')
            ->assertSee('Active')
            ->assertSee('Edit')
            ->assertSee('Delete');

        $school = \App\Models\School::where('title', 'Green Valley School')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.schools.update', $school), [
                'title' => 'Updated Green Valley School',
                'address' => 'Updated Road, Dhaka',
                'status' => 'pending',
            ])
            ->assertRedirect(route('admin.schools.index'))
            ->assertSessionHas('status', 'School updated.');

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'title' => 'Updated Green Valley School',
            'address' => 'Updated Road, Dhaka',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.schools.destroy', $school))
            ->assertRedirect(route('admin.schools.index'))
            ->assertSessionHas('status', 'School deleted.');

        $this->assertDatabaseMissing('schools', [
            'id' => $school->id,
        ]);
    }

    public function test_student_cannot_access_schools_admin_pages(): void
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get(route('admin.schools.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('admin.schools.store'), [
                'title' => 'Blocked School',
                'address' => 'Blocked address',
                'status' => 'pending',
            ])
            ->assertForbidden();

        $school = \App\Models\School::create([
            'title' => 'Protected School',
            'address' => 'Protected address',
            'status' => 'active',
        ]);

        $this->actingAs($student)
            ->patch(route('admin.schools.update', $school), [
                'title' => 'Hacked School',
                'address' => 'Hacked address',
                'status' => 'pending',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->delete(route('admin.schools.destroy', $school))
            ->assertForbidden();
    }

    public function test_admin_can_access_question_add_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/questions/create')
            ->assertOk()
            ->assertSee('Question Add')
            ->assertSee('Select class')
            ->assertSee('Select subject')
            ->assertSee('Select oddhay / chapter');
    }

    public function test_admin_can_manage_academic_structure(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post('/admin/classes', ['name' => 'Class 10'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post('/admin/subjects', ['name' => 'Physics'])
            ->assertRedirect();

        $classId = \App\Models\AcademicClass::where('name', 'Class 10')->value('id');
        $subjectId = \App\Models\Subject::where('name', 'Physics')->value('id');

        $this->actingAs($admin)
            ->post('/admin/chapters', [
                'academic_class_id' => $classId,
                'subject_id' => $subjectId,
                'chapter_no' => '1',
                'name' => 'Chapter 1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('academic_classes', ['name' => 'Class 10']);
        $this->assertDatabaseHas('subjects', ['name' => 'Physics']);
        $this->assertDatabaseHas('chapters', [
            'academic_class_id' => $classId,
            'subject_id' => $subjectId,
            'chapter_no' => '1',
            'name' => 'Chapter 1',
        ]);
    }

    public function test_admin_can_add_question_for_matching_class_subject_and_chapter(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);

        $this->actingAs($admin)
            ->post('/questions', [
                'academic_class_id' => $class->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'question_text' => 'What is the correct answer?',
                'options' => ['A', 'B', null, null],
                'correct_option' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Question added.');

        $this->assertDatabaseHas('questions', [
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the correct answer?',
        ]);
    }

    public function test_admin_can_bulk_add_questions_from_text_format(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Physics']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Motion',
        ]);

        $bulkQuestions = <<<TEXT
1. কোনো বস্তুর অবস্থান পরিবর্তনের হারকে কী বলে?

A) বল
B) বেগ
C) ত্বরণ
D) কাজ

উত্তর: B) বেগ

2. SI এককে বেগের একক কী?

A) m/s
B) km/h
C) m/s²
D) N

উত্তর: A) m/s
TEXT;

        $this->actingAs($admin)
            ->post(route('questions.bulk.store'), [
                'academic_class_id' => $class->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'bulk_questions' => $bulkQuestions,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', '2 questions added.');

        $this->assertDatabaseHas('questions', [
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'কোনো বস্তুর অবস্থান পরিবর্তনের হারকে কী বলে?',
        ]);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'SI এককে বেগের একক কী?',
            'sort_order' => 2,
        ]);
        $this->assertDatabaseHas('question_options', [
            'option_text' => 'বেগ',
            'is_correct' => true,
        ]);
        $this->assertDatabaseHas('question_options', [
            'option_text' => 'm/s',
            'is_correct' => true,
        ]);
    }

    public function test_admin_bulk_add_skips_existing_and_repeated_questions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Physics']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Motion',
        ]);

        \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'Existing question?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $bulkQuestions = <<<TEXT
1. Existing question?
A) One
B) Two
Answer: A

2. New question?
A) One
B) Two
Answer: B

3. New question?
A) One
B) Two
Answer: B
TEXT;

        $this->actingAs($admin)
            ->post(route('questions.bulk.store'), [
                'academic_class_id' => $class->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'bulk_questions' => $bulkQuestions,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', '1 questions added. 2 skipped.');

        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseHas('questions', [
            'question_text' => 'New question?',
            'sort_order' => 2,
        ]);
    }

    public function test_admin_cannot_add_question_with_mismatched_chapter(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $classFive = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $classSix = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $classFive->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);

        $this->actingAs($admin)
            ->post('/questions', [
                'academic_class_id' => $classSix->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'question_text' => 'This should fail.',
                'options' => ['A', 'B', null, null],
                'correct_option' => 0,
            ])
            ->assertSessionHasErrors([
            'chapter_id' => 'Selected class and subject অনুযায়ী সঠিক oddhay / chapter select করুন।',
            ]);

        $this->assertDatabaseMissing('questions', [
            'question_text' => 'This should fail.',
        ]);
    }

    public function test_admin_can_fetch_only_related_chapters_for_selected_class_and_subject(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $classFive = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $classSix = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $bangla = \App\Models\Subject::create(['name' => 'Bangla']);
        $english = \App\Models\Subject::create(['name' => 'English']);

        $matchingChapter = \App\Models\Chapter::create([
            'academic_class_id' => $classFive->id,
            'subject_id' => $bangla->id,
            'name' => 'Matching Chapter',
        ]);

        \App\Models\Chapter::create([
            'academic_class_id' => $classSix->id,
            'subject_id' => $bangla->id,
            'name' => 'Wrong Class Chapter',
        ]);

        \App\Models\Chapter::create([
            'academic_class_id' => $classFive->id,
            'subject_id' => $english->id,
            'name' => 'Wrong Subject Chapter',
        ]);

        $this->actingAs($admin)
            ->getJson('/admin/academic/chapters/options?academic_class_id=' . $classFive->id . '&subject_id=' . $bangla->id)
            ->assertOk()
            ->assertJson([
                'chapters' => [
                    [
                        'id' => $matchingChapter->id,
                        'name' => 'Matching Chapter',
                    ],
                ],
            ])
            ->assertJsonMissing(['name' => 'Wrong Class Chapter'])
            ->assertJsonMissing(['name' => 'Wrong Subject Chapter']);
    }

    public function test_student_can_view_own_exam_attempt_result(): void
    {
        $student = User::factory()->create(['is_admin' => false]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $student->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Test Exam',
        ]);
        $attempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 0,
            'total_marks' => 0,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('exam-attempts.result', $attempt))
            ->assertOk();
    }

    public function test_student_cannot_view_another_students_exam_attempt_result(): void
    {
        $owner = User::factory()->create(['is_admin' => false]);
        $otherStudent = User::factory()->create(['is_admin' => false]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $owner->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Test Exam',
        ]);
        $attempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $owner->id,
            'status' => 'graded',
            'score' => 0,
            'total_marks' => 0,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($otherStudent)
            ->get(route('exam-attempts.result', $attempt))
            ->assertForbidden();
    }

    public function test_admin_can_view_results_for_a_single_exam_from_exam_list(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $firstStudent = User::factory()->create(['name' => 'First Student', 'is_admin' => false]);
        $thirdStudent = User::factory()->create(['name' => 'Third Student', 'is_admin' => false]);
        $secondStudent = User::factory()->create(['name' => 'Second Student', 'is_admin' => false]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $selectedExam = \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Selected Exam',
        ]);
        $otherExam = \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Other Exam',
        ]);

        \App\Models\ExamAttempt::create([
            'exam_id' => $selectedExam->id,
            'user_id' => $firstStudent->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $selectedExam->id,
            'user_id' => $firstStudent->id,
            'status' => 'graded',
            'score' => 7,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $selectedExam->id,
            'user_id' => $thirdStudent->id,
            'status' => 'graded',
            'score' => 6,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $otherExam->id,
            'user_id' => $secondStudent->id,
            'status' => 'graded',
            'score' => 5,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.exams.index'))
            ->assertOk()
            ->assertSee(route('admin.exams.results', $selectedExam));

        $this->actingAs($admin)
            ->get(route('admin.exams.results', $selectedExam))
            ->assertOk()
            ->assertSee('Selected Exam Results')
            ->assertSee('Total Participants')
            ->assertSee('>3<', false)
            ->assertSee('Highest Mark')
            ->assertDontSee('Top Position')
            ->assertSee('Position')
            ->assertSee('First Student')
            ->assertSee('Third Student')
            ->assertSee('8 / 10')
            ->assertSee('7 / 10')
            ->assertSee('6 / 10')
            ->assertDontSee('Second Student')
            ->assertDontSee('Other Exam');
    }

    public function test_exam_results_use_dense_positions_after_tied_scores(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create(['name' => 'Current Student', 'academic_class_id' => $class->id, 'is_admin' => false]);
        $secondStudent = User::factory()->create(['name' => 'Second Student', 'academic_class_id' => $class->id, 'is_admin' => false]);
        $thirdStudent = User::factory()->create(['name' => 'Third Student', 'academic_class_id' => $class->id, 'is_admin' => false]);
        $fourthStudent = User::factory()->create(['name' => 'Fourth Student', 'academic_class_id' => $class->id, 'is_admin' => false]);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $student->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Dense Ranking Exam',
        ]);

        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 1,
            'total_marks' => 1,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $secondStudent->id,
            'status' => 'graded',
            'score' => 1,
            'total_marks' => 1,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $thirdStudent->id,
            'status' => 'graded',
            'score' => 1,
            'total_marks' => 1,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $fourthStudent->id,
            'status' => 'graded',
            'score' => 0,
            'total_marks' => 1,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('exams.results', $exam))
            ->assertOk()
            ->assertSeeInOrder([
                '<td>1</td>',
                'Current Student',
                '<td>1</td>',
                'Second Student',
                '<td>1</td>',
                'Third Student',
                '<td>2</td>',
                'Fourth Student',
            ], false)
            ->assertDontSee('<td>4</td>', false);
    }

}

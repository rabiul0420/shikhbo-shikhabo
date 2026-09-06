<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_guest_top_menu_shows_public_pages_and_auth_links(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('About')
            ->assertSee('Contact')
            ->assertSee('Privacy Policy')
            ->assertSee('Gift Winners')
            ->assertSee('Customize Exam')
            ->assertSee('Login')
            ->assertSee('Register')
            ->assertSee(route('login', ['redirect_to' => route('custom-exams.create', [], false)]))
            ->assertDontSee('Take Exam')
            ->assertDontSee('My Profile')
            ->assertDontSee('Logout');
    }

    public function test_logged_in_top_menu_shows_profile_and_logout(): void
    {
        $student = User::factory()->create(['is_admin' => false]);

        $this->actingAs($student)
            ->get('/')
            ->assertOk()
            ->assertSee('Home')
            ->assertSee('About')
            ->assertSee('Contact')
            ->assertSee('Privacy Policy')
            ->assertSee('Gift Winners')
            ->assertSee('My Profile')
            ->assertSee('My Result')
            ->assertSee('Logout')
            ->assertDontSee('Take Exam')
            ->assertDontSee('Login')
            ->assertDontSee('Register');
    }

    public function test_bangla_locale_prefix_serves_bangla_content(): void
    {
        $this->get('/bn')
            ->assertOk()
            ->assertSee('হোম')
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="bn"', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('Home')
            ->assertDontSee('>হোম<', false);
    }

    public function test_published_blog_shows_localized_content_and_footer_link(): void
    {
        $blog = \App\Models\Blog::create([
            'title_en' => 'English Blog Title',
            'title_bn' => 'বাংলা ব্লগ শিরোনাম',
            'excerpt_en' => 'English excerpt',
            'excerpt_bn' => 'বাংলা সারাংশ',
            'body_en' => '<p>English body content</p>',
            'body_bn' => '<p>বাংলা বিস্তারিত কন্টেন্ট</p>',
            'meta_title_en' => 'English Meta',
            'meta_title_bn' => 'বাংলা মেটা',
            'meta_description_en' => 'English meta description',
            'meta_description_bn' => 'বাংলা মেটা বর্ণনা',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee(route('blog.index'))
            ->assertSee('Blog');

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('English Blog Title')
            ->assertSee('English excerpt');

        $this->get(route('blog.show', $blog))
            ->assertOk()
            ->assertSee('English Blog Title')
            ->assertSee('English body content')
            ->assertSee('English Meta', false);

        app()->setLocale('bn');

        $this->get('/bn/blog')
            ->assertOk()
            ->assertSee('বাংলা ব্লগ শিরোনাম')
            ->assertSee('বাংলা সারাংশ');

        $this->get('/bn/blog/'.$blog->slug)
            ->assertOk()
            ->assertSee('বাংলা ব্লগ শিরোনাম')
            ->assertSee('বাংলা বিস্তারিত কন্টেন্ট');
    }

    public function test_admin_can_create_blog_post(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.blogs.store'), [
                'title_en' => 'New Guide',
                'title_bn' => 'নতুন গাইড',
                'excerpt_en' => 'Short EN',
                'excerpt_bn' => 'সংক্ষিপ্ত বাংলা',
                'body_en' => '<p>Details EN</p>',
                'body_bn' => '<p>বিস্তারিত বাংলা</p>',
                'meta_title_en' => 'Meta EN',
                'meta_title_bn' => 'মেটা বাংলা',
                'meta_description_en' => 'Meta desc EN',
                'meta_description_bn' => 'মেটা বর্ণনা',
                'status' => 'published',
                'custom_css' => '.blog-article h1 { color: #1d4ed8; }',
                'json_schema' => json_encode([
                    '@type' => 'FAQPage',
                    'mainEntity' => [],
                ]),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blogs', [
            'title_en' => 'New Guide',
            'title_bn' => 'নতুন গাইড',
            'status' => 'published',
            'slug' => 'new-guide',
            'custom_css' => '.blog-article h1 { color: #1d4ed8; }',
        ]);

        $blog = \App\Models\Blog::query()->where('slug', 'new-guide')->first();
        $this->assertNotNull($blog);
        $this->assertSame('FAQPage', $blog->decodedJsonSchema()['@type']);
    }

    public function test_admin_blog_rejects_invalid_json_schema(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.blogs.create'))
            ->post(route('admin.blogs.store'), [
                'title_en' => 'Broken Schema',
                'title_bn' => 'ভুল স্কিমা',
                'body_en' => '<p>Details EN</p>',
                'body_bn' => '<p>বিস্তারিত বাংলা</p>',
                'status' => 'draft',
                'json_schema' => '{not-json',
            ])
            ->assertRedirect(route('admin.blogs.create'))
            ->assertSessionHasErrors('json_schema');
    }

    public function test_published_blog_renders_custom_css_and_json_schema(): void
    {
        $blog = \App\Models\Blog::create([
            'title_en' => 'Styled Post',
            'title_bn' => 'স্টাইল পোস্ট',
            'body_en' => '<p>English body</p>',
            'body_bn' => '<p>বাংলা বিস্তারিত</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'custom_css' => '.blog-article h1 { color: #dc2626; }',
            'json_schema' => json_encode([
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'What is this?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'A test.',
                        ],
                    ],
                ],
            ]),
        ]);

        $this->get(route('blog.show', $blog))
            ->assertOk()
            ->assertSee('id="blog-custom-css"', false)
            ->assertSee('.blog-article h1 { color: #dc2626; }', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('What is this?', false);
    }

    public function test_student_login_uses_mobile_number(): void
    {
        $student = User::factory()->create([
            'email' => 'student-login@example.com',
            'phone' => '01712345678',
            'is_admin' => false,
        ]);

        $this->post(route('login'), [
            'phone' => '01712345678',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_admin_login_uses_email(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-login@example.com',
            'phone' => '01787654321',
            'is_admin' => true,
        ]);

        $this->post(route('admin.login'), [
            'email' => 'admin-login@example.com',
            'password' => 'password',
        ])->assertRedirect(route('admin.index'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_guest_admin_pages_redirect_to_admin_login(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_logout_redirects_to_admin_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('logout'))
            ->assertRedirect(route('admin.login'));
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

        $this->get('/my-profile')->assertRedirect(route('login', ['redirect_to' => '/my-profile']));

        $this->actingAs($student)
            ->get('/my-profile')
            ->assertOk()
            ->assertSee('My Profile')
            ->assertSee('Student User')
            ->assertSee('01712345678')
            ->assertSee('Student')
            ->assertSee('Class 5')
            ->assertSee(route('profile.edit'))
            ->assertSee('Update Profile')
            ->assertDontSee('Email');
    }

    public function test_profile_update_button_opens_update_profile_form(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        \App\Models\School::create([
            'title' => 'Student School',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);
        $student = User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);

        $this->actingAs($student)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Update Profile')
            ->assertSee('Mobile Number')
            ->assertSee('Profile Picture')
            ->assertSee('Student School')
            ->assertSee(route('profile.update'));
    }

    public function test_logged_in_user_can_update_profile_from_profile_page(): void
    {
        $oldClass = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $newClass = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $oldSchool = \App\Models\School::create([
            'title' => 'Old School',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);
        $newSchool = \App\Models\School::create([
            'title' => 'New School',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);
        $student = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old-profile@example.com',
            'phone' => '01700000000',
            'academic_class_id' => $oldClass->id,
            'school_id' => $oldSchool->id,
            'is_admin' => false,
        ]);

        $profilePhoto = UploadedFile::fake()->createWithContent(
            'profile.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        $this->actingAs($student)
            ->patch('/my-profile', [
                'name' => 'Updated Name',
                'phone' => '01800000000',
                'academic_class_id' => $newClass->id,
                'school_name' => 'New School',
                'profile_photo' => $profilePhoto,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Profile updated.');

        $student->refresh();

        $this->assertSame('Updated Name', $student->name);
        $this->assertSame('old-profile@example.com', $student->email);
        $this->assertSame('01800000000', $student->phone);
        $this->assertSame($newClass->id, $student->academic_class_id);
        $this->assertSame($newSchool->id, $student->school_id);
        $this->assertNotNull($student->profile_photo_path);
        $this->assertStringStartsWith('uploads/profile-photos/', $student->profile_photo_path);
        $this->assertFileExists(public_path($student->profile_photo_path));
        @unlink(public_path($student->profile_photo_path));
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

        $this->get('/my-results')->assertRedirect(route('login', ['redirect_to' => '/my-results']));

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
            'first_prize' => 'Trophy',
            'second_prize' => 'Medal',
            'third_prize' => 'Gift box',
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
            ->assertSee('Trophy')
            ->assertSee('Medal')
            ->assertSee(route('exam-attempts.result', $ownAttempt))
            ->assertDontSee(route('exam-attempts.result', $otherAttempt))
            ->assertSee('Details private');
    }

    public function test_student_can_view_all_results_for_an_exam_they_attempted(): void
    {
        $examClass = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $currentClass = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $student = User::factory()->create([
            'name' => 'Current Student',
            'academic_class_id' => $currentClass->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $examClass->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $student->id,
            'academic_class_id' => $examClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Attempted Exam Results',
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

        $this->actingAs($student)
            ->get(route('exams.results', $exam))
            ->assertOk()
            ->assertSee('Attempted Exam Results')
            ->assertSee('Current Student');
    }

    public function test_public_info_pages_are_available(): void
    {
        $this->get('/about-us')->assertOk()->assertSee('About Us');
        $this->get('/contact-us')->assertOk()->assertSee('Contact Us');
        $this->get('/privacy-policy')->assertOk()->assertSee('Privacy Policy');
        $this->get('/how-to-take-bd-model-test-online')
            ->assertOk()
            ->assertSee('How to Take a BD Model Test Online')
            ->assertSee('How to Take a BD Model Test Online | Step-by-Step Guide')
            ->assertSee('Step-by-step guide to take a BD model test online', false);
        $this->get('/how-to-give-a-model-test')
            ->assertRedirect('/how-to-take-bd-model-test-online');
        $this->get('/bn/how-to-take-bd-model-test-online')
            ->assertOk()
            ->assertSee('অনলাইনে বিডি মডেল টেস্ট কীভাবে দিবেন');
        $this->get('/bn/how-to-give-a-model-test')
            ->assertRedirect('/bn/how-to-take-bd-model-test-online');
    }

    public function test_sitemap_includes_only_public_indexable_routes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $classWithExams = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $emptyClass = \App\Models\AcademicClass::create(['name' => 'Empty Class']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $classWithExams->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);
        $exam = \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $classWithExams->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Sitemap Public Exam',
            'starts_at' => today(),
            'ends_at' => today()->addDay(),
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $classWithExams->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'Sitemap question?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);

        $publishedBlog = \App\Models\Blog::create([
            'title_en' => 'Published Sitemap Post',
            'title_bn' => 'প্রকাশিত পোস্ট',
            'body_en' => '<p>English</p>',
            'body_bn' => '<p>বাংলা</p>',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);
        $draftBlog = \App\Models\Blog::create([
            'title_en' => 'Draft Sitemap Post',
            'title_bn' => 'খসড়া পোস্ট',
            'body_en' => '<p>Draft EN</p>',
            'body_bn' => '<p>খসড়া</p>',
            'status' => 'draft',
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $xml = $response->getContent();

        $response->assertSee(localized_route('home', [], 'en'), false)
            ->assertSee(localized_route('home', [], 'bn'), false)
            ->assertSee(localized_route('exams.directory', [], 'en'), false)
            ->assertSee(localized_route('blog.index', [], 'en'), false)
            ->assertSee(localized_route('how-to-take-bd-model-test-online', [], 'en'), false)
            ->assertSee(localized_route('how-to-take-bd-model-test-online', [], 'bn'), false)
            ->assertSee(localized_route('about-us', [], 'en'), false)
            ->assertSee(localized_route('contact-us', [], 'en'), false)
            ->assertSee(localized_route('privacy-policy', [], 'en'), false)
            ->assertSee(localized_route('classes.exams', ['classSlug' => $classWithExams->slug], 'en'), false)
            ->assertSee(localized_route('exams.show', ['examSlug' => $exam->slug], 'en'), false)
            ->assertSee(localized_route('blog.show', ['blog' => $publishedBlog->slug], 'en'), false)
            ->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false)
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertSee('<loc>', false)
            ->assertSee('</urlset>', false)
            ->assertDontSee('xmlns:xhtml', false)
            ->assertDontSee('xhtml:link', false);

        $this->assertStringNotContainsString(localized_route('classes.exams', ['classSlug' => $emptyClass->slug], 'en'), $xml);
        $this->assertStringNotContainsString($draftBlog->slug, $xml);
        $this->assertStringNotContainsString('/login', $xml);
        $this->assertStringNotContainsString('/register', $xml);
        $this->assertStringNotContainsString('/admin', $xml);
        $this->assertStringNotContainsString('/my-profile', $xml);
        $this->assertStringNotContainsString('/my-results', $xml);
        $this->assertStringNotContainsString('/custom-exams', $xml);
        $this->assertStringNotContainsString('/exam-attempts', $xml);
    }

    public function test_admin_can_create_exam_with_random_question_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);

        $questions = collect(range(1, 3))->map(function ($index) use ($class, $subject, $chapter) {
            return \App\Models\Question::create([
                'academic_class_id' => $class->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'question_text' => 'Random question ' . $index,
                'type' => 'single_choice',
                'marks' => 1,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        });

        $this->actingAs($admin)
            ->post(route('exams.store'), [
                'title' => 'Random Question Exam',
                'academic_class_id' => $class->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'starts_at' => today()->format('Y-m-d'),
                'ends_at' => today()->format('Y-m-d'),
                'duration_minutes' => 30,
                'question_selection_mode' => 'random',
                'random_question_count' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Exam added.');

        $exam = \App\Models\Exam::where('title', 'Random Question Exam')->firstOrFail();
        $attachedQuestionIds = $exam->questions()->pluck('questions.id')->all();

        $this->assertCount(2, $attachedQuestionIds);
        $this->assertEmpty(array_diff($attachedQuestionIds, $questions->pluck('id')->all()));
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
            ->assertSee('Showing exams for Class 5.')
            ->assertDontSee('Class Six Exam');
    }

    public function test_home_page_replaces_start_exam_for_already_participated_exam(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
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
            'title' => 'Already Completed Exam',
            'starts_at' => today(),
            'ends_at' => today(),
            'duration_minutes' => 30,
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
            ->get('/')
            ->assertOk()
            ->assertSee('Already Completed Exam')
            ->assertSee('Already participated')
            ->assertSee('View result')
            ->assertSee(route('exam-attempts.result', $attempt))
            ->assertDontSee('Start exam');
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

    public function test_guest_cannot_preview_exam_questions_or_submit_answers(): void
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
            'title' => 'Public Preview Exam',
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the answer?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);
        \App\Models\QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Correct option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $this->get(route('exams.show', $exam))
            ->assertOk()
            ->assertSee('Exam preview')
            ->assertSee('Login to participate')
            ->assertSee('1 questions')
            ->assertSee('1 marks')
            ->assertDontSee('What is the answer?')
            ->assertDontSee('Correct option')
            ->assertDontSee('Submit answers');

        $this->post(route('exams.submit', $exam), [
            'answers' => [$question->id => [1]],
        ])->assertRedirect('/login');
    }

    public function test_login_can_redirect_back_to_exam_preview(): void
    {
        $student = User::factory()->create([
            'email' => 'preview-student@example.com',
            'phone' => '01733333333',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);
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
            'title' => 'Redirect Preview Exam',
        ]);

        $this->get(route('login', ['redirect_to' => route('exams.show', $exam, false)]))
            ->assertOk()
            ->assertSee('name="redirect_to"', false)
            ->assertSee(route('exams.show', $exam, false), false);

        $this->post(route('login'), [
            'phone' => '01733333333',
            'password' => 'password',
            'redirect_to' => route('exams.show', $exam, false),
        ])->assertRedirect(route('exams.show', $exam, false));
    }

    public function test_login_can_redirect_to_customize_exam_page(): void
    {
        $student = User::factory()->create([
            'email' => 'custom-login@example.com',
            'phone' => '01744444444',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Customize Exam')
            ->assertSee(route('login', ['redirect_to' => route('custom-exams.create', [], false)]));

        $this->get(route('login', ['redirect_to' => route('custom-exams.create', [], false)]))
            ->assertOk()
            ->assertSee('name="redirect_to"', false)
            ->assertSee(route('custom-exams.create', [], false), false);

        $this->post(route('login'), [
            'phone' => '01744444444',
            'password' => 'password',
            'redirect_to' => route('custom-exams.create', [], false),
        ])->assertRedirect(route('custom-exams.create', [], false));

        $this->assertAuthenticatedAs($student);
    }

    public function test_home_page_groups_exams_by_schedule_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $subject = \App\Models\Subject::create(['name' => 'Bangla']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'name' => 'Chapter 1',
        ]);

        \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Running Schedule Exam',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);
        \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Upcoming Schedule Exam',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);
        \App\Models\Exam::create([
            'created_by' => $admin->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Expired Schedule Exam',
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->get(route('home'));
        $response->assertOk()
            ->assertSee('Available Model Test')
            ->assertSee('Upcoming Model Test')
            ->assertDontSee('>Expired Model Test<', false)
            ->assertSee('Running Schedule Exam')
            ->assertSee('Upcoming Schedule Exam')
            ->assertSee('Expired Schedule Exam');

        $html = $response->getContent();
        $availablePos = strpos($html, 'id="status-running"');
        $expiredExamPos = strpos($html, 'Expired Schedule Exam');
        $upcomingPos = strpos($html, 'id="status-upcoming"');
        $upcomingExamPos = strpos($html, 'Upcoming Schedule Exam');

        $this->assertNotFalse($availablePos);
        $this->assertNotFalse($expiredExamPos);
        $this->assertNotFalse($upcomingPos);
        $this->assertNotFalse($upcomingExamPos);
        $this->assertTrue($availablePos < $expiredExamPos, 'Past-deadline exam should appear under Available');
        $this->assertTrue($expiredExamPos < $upcomingPos, 'Available exams should appear before Upcoming section');
        $this->assertTrue($upcomingPos < $upcomingExamPos);
    }

    public function test_student_cannot_submit_exam_outside_running_schedule(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
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
            'title' => 'Future Exam',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the answer?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);
        $option = \App\Models\QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Correct option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($student)
            ->get(route('exams.show', $exam))
            ->assertOk()
            ->assertSee('Exam preview')
            ->assertSee('Questions are available only after the exam starts.')
            ->assertDontSee('Submit answers');

        $this->actingAs($student)
            ->post(route('exams.submit', $exam), [
                'answers' => [$question->id => [$option->id]],
            ])
            ->assertSessionHasErrors('exam');

        $this->assertDatabaseMissing('exam_attempts', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_student_must_submit_exam_within_duration(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
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
            'title' => 'Timed Exam',
            'starts_at' => today(),
            'ends_at' => today(),
            'duration_minutes' => 30,
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the answer?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);
        $option = \App\Models\QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Correct option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($student)
            ->get(route('exams.show', $exam))
            ->assertOk()
            ->assertSee('Finish this exam within 30 minutes')
            ->assertSee('data-auto-submit="true"', false)
            ->assertSee('HTMLFormElement.prototype.submit.call(examForm)', false)
            ->assertSee('name="auto_submitted"', false)
            ->assertSee('setTimeout(autoSubmitExam', false)
            ->assertSessionHas('exam_started_at.' . $exam->id);

        $this->actingAs($student)
            ->post(route('exams.submit', $exam), [
                'answers' => [$question->id => [$option->id]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'score' => 1,
        ]);
    }

    public function test_student_cannot_submit_after_exam_duration_expires(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
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
            'title' => 'Expired Timed Exam',
            'starts_at' => today(),
            'ends_at' => today(),
            'duration_minutes' => 30,
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the answer?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);
        $option = \App\Models\QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Correct option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($student)
            ->withSession(['exam_started_at.' . $exam->id => now()->subMinutes(31)->toIso8601String()])
            ->post(route('exams.submit', $exam), [
                'answers' => [$question->id => [$option->id]],
            ])
            ->assertSessionHasErrors('exam');

        $this->assertDatabaseMissing('exam_attempts', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_auto_submit_is_accepted_shortly_after_exam_duration_expires(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
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
            'title' => 'Auto Submit Timed Exam',
            'starts_at' => today(),
            'ends_at' => today(),
            'duration_minutes' => 30,
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the answer?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);
        $option = \App\Models\QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Correct option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($student)
            ->withSession(['exam_started_at.' . $exam->id => now()->subMinutes(30)->subSeconds(20)->toIso8601String()])
            ->post(route('exams.submit', $exam), [
                'auto_submitted' => '1',
                'answers' => [$question->id => [$option->id]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'score' => 1,
        ]);
    }

    public function test_student_can_participate_in_an_exam_only_once(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
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
            'title' => 'Single Attempt Exam',
            'starts_at' => today(),
            'ends_at' => today(),
            'duration_minutes' => 30,
        ]);
        $question = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'What is the answer?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $exam->questions()->attach($question);
        $option = \App\Models\QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Correct option',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $attempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 1,
            'total_marks' => 1,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('exams.show', $exam))
            ->assertOk()
            ->assertSee('You have already participated in this exam')
            ->assertSee(route('exam-attempts.result', $attempt))
            ->assertDontSee('Submit answers');

        $this->actingAs($student)
            ->withSession(['exam_started_at.' . $exam->id => now()->toIso8601String()])
            ->post(route('exams.submit', $exam), [
                'answers' => [$question->id => [$option->id]],
            ])
            ->assertRedirect(route('exam-attempts.result', $attempt))
            ->assertSessionHas('status', 'You have already participated in this exam.');

        $this->assertDatabaseCount('exam_attempts', 1);
    }

    public function test_database_prevents_duplicate_exam_attempts_for_same_student(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
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
            'title' => 'Unique Attempt Exam',
            'starts_at' => today(),
            'ends_at' => today(),
            'duration_minutes' => 30,
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

        $this->expectException(\Illuminate\Database\QueryException::class);

        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 0,
            'total_marks' => 1,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
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
            ->assertSee('Mobile Number')
            ->assertSee('Class')
            ->assertSee('Class 5')
            ->assertSee('School')
            ->assertSee('Registration School');

        $this->post('/register', [
            'name' => 'New Student',
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
            'phone' => '01811111111',
            'school_id' => $school->id,
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
    }

    public function test_student_can_register_with_profile_picture(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 7']);
        \App\Models\School::create([
            'title' => 'Photo School',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);

        $profilePhoto = UploadedFile::fake()->createWithContent(
            'profile.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=')
        );

        $this->post('/register', [
            'name' => 'Photo Student',
            'phone' => '01911111111',
            'academic_class_id' => $class->id,
            'school_name' => 'Photo School',
            'profile_photo' => $profilePhoto,
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Account created.');

        $user = User::where('phone', '01911111111')->firstOrFail();

        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringStartsWith('uploads/profile-photos/', $user->profile_photo_path);
        $this->assertFileExists(public_path($user->profile_photo_path));
        @unlink(public_path($user->profile_photo_path));
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
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
            ->assertSee('students-table')
            ->assertSee('admin\/students\/data', false)
            ->assertSee('Change Password')
            ->assertSee('>1<', false)
            ->assertDontSee('admin@example.com');

        $this->actingAs($admin)
            ->getJson(route('admin.students.data', [
                'draw' => 1,
                'start' => 0,
                'length' => 10,
            ]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonFragment(['Student User'])
            ->assertJsonFragment(['student@example.com'])
            ->assertJsonFragment(['01712345678'])
            ->assertJsonFragment(['Class 5'])
            ->assertJsonFragment(['Student School']);
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
            'user_id' => $secondStudent->id,
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
            ->assertSee('Second Student')
            ->assertSee('Third Student')
            ->assertSee('8 / 10')
            ->assertSee('7 / 10')
            ->assertSee('6 / 10')
            ->assertDontSee('Other Exam');
    }

    public function test_admin_can_view_and_mark_gift_recipients(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $firstStudent = User::factory()->create([
            'name' => 'First Gift Student',
            'phone' => '01711111111',
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
        $secondStudent = User::factory()->create([
            'name' => 'Second Gift Student',
            'phone' => '01811111111',
            'academic_class_id' => $class->id,
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
            'title' => 'Gift Exam',
            'first_prize' => '50 tk recharge',
            'second_prize' => '30 tk recharge',
            'third_prize' => '20 tk recharge',
        ]);
        $firstAttempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $firstStudent->id,
            'status' => 'graded',
            'score' => 10,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $secondStudent->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.gift-recipients.index'))
            ->assertOk()
            ->assertSee('Gift Recipient List')
            ->assertSee('gift-recipients-table')
            ->assertSee('dataTables.min.js')
            ->assertSee('First Gift Student')
            ->assertSee('01711111111')
            ->assertSee('50 tk recharge')
            ->assertSee('Second Gift Student')
            ->assertSee('30 tk recharge')
            ->assertSee('Pending')
            ->assertSee('Mark given');

        $this->actingAs($admin)
            ->patch(route('admin.gift-recipients.given', $firstAttempt))
            ->assertRedirect()
            ->assertSessionHas('status', 'Gift marked as given.');

        $this->assertDatabaseHas('gift_awards', [
            'exam_attempt_id' => $firstAttempt->id,
            'position' => 1,
            'gift_title' => '50 tk recharge',
            'status' => 'given',
            'given_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.gift-recipients.index'))
            ->assertOk()
            ->assertSee('Given')
            ->assertSee('Completed');
    }

    public function test_home_page_shows_only_given_gift_recipients(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $school = \App\Models\School::create([
            'title' => 'Public Winner School',
            'address' => 'Dhaka',
            'status' => 'active',
        ]);
        $winner = User::factory()->create([
            'name' => 'Public Gift Winner',
            'academic_class_id' => $class->id,
            'school_id' => $school->id,
            'profile_photo_path' => 'uploads/profile-photos/winner.jpg',
            'is_admin' => false,
        ]);
        $pendingWinner = User::factory()->create([
            'name' => 'Pending Gift Winner',
            'academic_class_id' => $class->id,
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
            'title' => 'Public Gift Exam',
            'first_prize' => '50 tk recharge',
            'second_prize' => '30 tk recharge',
        ]);
        $givenAttempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $winner->id,
            'status' => 'graded',
            'score' => 10,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);
        $pendingAttempt = \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $pendingWinner->id,
            'status' => 'graded',
            'score' => 8,
            'total_marks' => 10,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        \App\Models\GiftAward::create([
            'exam_attempt_id' => $givenAttempt->id,
            'position' => 1,
            'gift_title' => '50 tk recharge',
            'status' => 'given',
            'given_at' => now(),
            'given_by' => $admin->id,
        ]);
        \App\Models\GiftAward::create([
            'exam_attempt_id' => $pendingAttempt->id,
            'position' => 2,
            'gift_title' => '30 tk recharge',
            'status' => 'pending',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Gift Received Students')
            ->assertSee('Public Gift Winner')
            ->assertSee('Public Winner School')
            ->assertSee('50 tk recharge')
            ->assertSee('Public Gift Exam')
            ->assertDontSee('Pending Gift Winner');
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

    public function test_student_can_create_and_submit_custom_exam(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Math']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_no' => '1',
            'name' => 'Algebra',
        ]);

        $firstQuestion = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => '2 + 2 = ?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $firstCorrectOption = $firstQuestion->options()->create([
            'option_text' => '4',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $firstQuestion->options()->create([
            'option_text' => '5',
            'is_correct' => false,
            'sort_order' => 2,
        ]);

        $secondQuestion = \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => '3 + 3 = ?',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $secondCorrectOption = $secondQuestion->options()->create([
            'option_text' => '6',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $secondQuestion->options()->create([
            'option_text' => '7',
            'is_correct' => false,
            'sort_order' => 2,
        ]);

        $this->actingAs($student)
            ->get(route('custom-exams.create'))
            ->assertOk()
            ->assertSee('Customize Exam')
            ->assertSee('Math');

        $this->actingAs($student)
            ->post(route('custom-exams.store'), [
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'question_count' => 2,
            ])
            ->assertRedirect();

        $customExam = \App\Models\CustomExam::firstOrFail();
        $this->assertSame(2, $customExam->questions()->count());

        $this->actingAs($student)
            ->get(route('custom-exams.show', $customExam))
            ->assertOk()
            ->assertSee('2 + 2 = ?')
            ->assertSee('3 + 3 = ?');

        $this->actingAs($student)
            ->post(route('custom-exams.submit', $customExam), [
                'answers' => [
                    $firstQuestion->id => [$firstCorrectOption->id],
                    $secondQuestion->id => [$secondCorrectOption->id],
                ],
            ])
            ->assertRedirect();

        $attempt = \App\Models\CustomExamAttempt::firstOrFail();

        $this->assertDatabaseHas('custom_exam_attempts', [
            'custom_exam_id' => $customExam->id,
            'user_id' => $student->id,
            'score' => 2,
            'total_marks' => 2,
        ]);

        $this->actingAs($student)
            ->get(route('custom-exam-attempts.result', $attempt))
            ->assertOk()
            ->assertSee('Custom Result')
            ->assertSee('2 / 2');
    }

    public function test_custom_exam_question_count_matches_selected_class_subject_and_chapter(): void
    {
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $otherClass = \App\Models\AcademicClass::create(['name' => 'Class 6']);
        $student = User::factory()->create([
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Math']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_no' => '1',
            'name' => 'Algebra',
        ]);
        $otherChapter = \App\Models\Chapter::create([
            'academic_class_id' => $otherClass->id,
            'subject_id' => $subject->id,
            'chapter_no' => '1',
            'name' => 'Algebra',
        ]);

        foreach (range(1, 3) as $index) {
            \App\Models\Question::create([
                'academic_class_id' => $class->id,
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
                'question_text' => 'Active question ' . $index,
                'type' => 'single_choice',
                'marks' => 1,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        \App\Models\Question::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'question_text' => 'Inactive question',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 4,
            'is_active' => false,
        ]);
        \App\Models\Question::create([
            'academic_class_id' => $otherClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $otherChapter->id,
            'question_text' => 'Wrong class question',
            'type' => 'single_choice',
            'marks' => 1,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->getJson(route('custom-exams.questions.count', [
                'subject_id' => $subject->id,
                'chapter_id' => $chapter->id,
            ]))
            ->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_admin_can_view_student_custom_exam_results(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $class = \App\Models\AcademicClass::create(['name' => 'Class 5']);
        $student = User::factory()->create([
            'name' => 'Custom Result Student',
            'academic_class_id' => $class->id,
            'is_admin' => false,
        ]);
        $subject = \App\Models\Subject::create(['name' => 'Science']);
        $chapter = \App\Models\Chapter::create([
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_no' => '2',
            'name' => 'Light',
        ]);
        $customExam = \App\Models\CustomExam::create([
            'user_id' => $student->id,
            'academic_class_id' => $class->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => 'Science - Light Custom Exam',
            'question_count' => 5,
            'total_marks' => 5,
        ]);
        $attempt = \App\Models\CustomExamAttempt::create([
            'custom_exam_id' => $customExam->id,
            'user_id' => $student->id,
            'status' => 'graded',
            'score' => 4,
            'total_marks' => 5,
            'started_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.custom-results.index'))
            ->assertOk()
            ->assertSee('Student Custom Exam Results')
            ->assertSee('Custom Result Student')
            ->assertSee('Science - Light Custom Exam')
            ->assertSee('4 / 5')
            ->assertSee(route('custom-exam-attempts.result', $attempt));
    }

}

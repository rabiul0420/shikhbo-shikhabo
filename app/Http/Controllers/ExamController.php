<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Chapter;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(): View
    {
        $examCount = Exam::query()->count();
        $resultExamIds = Exam::query()->pluck('id');
        $classes = AcademicClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->with('academicClasses')->orderBy('name')->get();

        return view('admin.exams.index', compact('examCount', 'resultExamIds', 'classes', 'subjects'));
    }

    public function data(Request $request): JsonResponse
    {
        $columns = [
            0 => 'title',
            1 => 'academic_class',
            2 => 'subject',
            3 => 'chapter',
            4 => 'starts_at',
            5 => 'duration_minutes',
            6 => 'offer',
            7 => 'questions_count',
        ];

        $search = trim((string) $request->input('search.value', ''));
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 10);
        $length = $length === -1 ? 100 : min(max($length, 1), 100);
        $orderColumn = $columns[(int) $request->input('order.0.column', 0)] ?? 'title';
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $baseQuery = Exam::query()
            ->with(['academicClass', 'subject', 'chapter'])
            ->withCount('questions');

        $total = (clone $baseQuery)->count();

        $filteredQuery = (clone $baseQuery)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('duration_minutes', 'like', "%{$search}%")
                        ->orWhere('first_prize', 'like', "%{$search}%")
                        ->orWhere('second_prize', 'like', "%{$search}%")
                        ->orWhere('third_prize', 'like', "%{$search}%")
                        ->orWhereHas('academicClass', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('chapter', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
                });
            });

        $filtered = (clone $filteredQuery)->count();

        $exams = $this->orderExamData($filteredQuery, $orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $exams->map(fn (Exam $exam) => [
                'title' => e($exam->title),
                'academic_class' => e($exam->academicClass->name ?? '-'),
                'subject' => e($exam->subject->name ?? '-'),
                'chapter' => e($exam->chapter->display_name ?? '-'),
                'schedule' => $this->scheduleHtml($exam),
                'duration' => $exam->duration_minutes ? e($exam->duration_minutes . ' mins') : '-',
                'offer' => $this->offerHtml($exam),
                'questions_count' => $exam->questions_count,
                'actions' => $this->actionsHtml($exam),
            ]),
        ]);
    }

    public function editData(Exam $exam): JsonResponse
    {
        $exam->load(['academicClass', 'subject', 'chapter'])->loadCount('questions');

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'academic_class_id' => $exam->academic_class_id,
                'subject_id' => $exam->subject_id,
                'chapter_id' => $exam->chapter_id,
                'starts_at' => optional($exam->starts_at)->format('Y-m-d'),
                'ends_at' => optional($exam->ends_at)->format('Y-m-d'),
                'duration_minutes' => $exam->duration_minutes,
                'first_prize' => $exam->first_prize,
                'second_prize' => $exam->second_prize,
                'third_prize' => $exam->third_prize,
                'questions_count' => $exam->questions_count,
                'question_ids' => $exam->questions()->pluck('questions.id')->map(fn ($id) => (string) $id)->values(),
                'update_url' => route('exams.update', $exam),
            ],
        ]);
    }

    public function questionOptions(Request $request): JsonResponse
    {
        $data = $request->validate([
            'academic_class_id' => ['required', 'integer', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                'integer',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_id' => ['required', 'integer', 'exists:chapters,id'],
        ]);

        $questions = Question::query()
            ->select(['id', 'question_text'])
            ->where('academic_class_id', $data['academic_class_id'])
            ->where('subject_id', $data['subject_id'])
            ->where('chapter_id', $data['chapter_id'])
            ->latest()
            ->get()
            ->map(fn (Question $question) => [
                'id' => $question->id,
                'text' => $question->question_text,
            ]);

        return response()->json(['questions' => $questions]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'question_selection_mode' => $request->input('question_selection_mode', 'manual'),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'first_prize' => ['nullable', 'string', 'max:255'],
            'second_prize' => ['nullable', 'string', 'max:255'],
            'third_prize' => ['nullable', 'string', 'max:255'],
            'question_selection_mode' => ['required', Rule::in(['manual', 'random'])],
            'random_question_count' => ['nullable', 'required_if:question_selection_mode,random', 'integer', 'min:1', 'max:500'],
            'question_ids' => ['nullable', 'required_if:question_selection_mode,manual', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        $academicClass = AcademicClass::findOrFail($data['academic_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = Chapter::query()
            ->whereKey($data['chapter_id'])
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->first();

        if (! $chapter) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Selected class and subject অনুযায়ী সঠিক oddhay / chapter select করুন।',
            ]);
        }

        if ($data['question_selection_mode'] === 'random') {
            $questionIds = $this->randomQuestionIds($data, $academicClass, $subject, $chapter);
        } else {
            $questionIds = Question::query()
                ->whereIn('id', $data['question_ids'])
                ->where('academic_class_id', $academicClass->id)
                ->where('subject_id', $subject->id)
                ->where('chapter_id', $chapter->id)
                ->pluck('id');

            if ($questionIds->count() !== count(array_unique($data['question_ids']))) {
                throw ValidationException::withMessages([
                    'question_ids' => 'Selected class, subject এবং oddhay / chapter অনুযায়ী question select করুন।',
                ]);
            }

        }

        $exam = Exam::create([
            'created_by' => $request->user()->id,
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'duration_minutes' => $data['duration_minutes'],
            'first_prize' => $data['first_prize'] ?? null,
            'second_prize' => $data['second_prize'] ?? null,
            'third_prize' => $data['third_prize'] ?? null,
        ]);

        $exam->questions()->sync($questionIds);

        return back()->with('status', 'Exam added.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return back()->with('status', 'Exam deleted.');
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $request->merge([
            'question_selection_mode' => $request->input('question_selection_mode', 'manual'),
        ]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'subject_id' => [
                'required',
                Rule::exists('academic_class_subject', 'subject_id')
                    ->where('academic_class_id', $request->input('academic_class_id')),
            ],
            'chapter_id' => ['required', 'exists:chapters,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'first_prize' => ['nullable', 'string', 'max:255'],
            'second_prize' => ['nullable', 'string', 'max:255'],
            'third_prize' => ['nullable', 'string', 'max:255'],
            'question_selection_mode' => ['required', Rule::in(['manual', 'random'])],
            'random_question_count' => ['nullable', 'required_if:question_selection_mode,random', 'integer', 'min:1', 'max:500'],
            'question_ids' => ['nullable', 'required_if:question_selection_mode,manual', 'array', 'min:1'],
            'question_ids.*' => ['integer', 'exists:questions,id'],
        ]);

        $academicClass = AcademicClass::findOrFail($data['academic_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $chapter = Chapter::query()
            ->whereKey($data['chapter_id'])
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->first();

        if (! $chapter) {
            throw ValidationException::withMessages([
                'chapter_id' => 'Selected class and subject অনুযায়ী সঠিক oddhay / chapter select করুন।',
            ]);
        }

        if ($data['question_selection_mode'] === 'random') {
            $questionIds = $this->randomQuestionIds($data, $academicClass, $subject, $chapter);
        } else {
            $questionIds = Question::query()
                ->whereIn('id', $data['question_ids'])
                ->where('academic_class_id', $academicClass->id)
                ->where('subject_id', $subject->id)
                ->where('chapter_id', $chapter->id)
                ->pluck('id');

            if ($questionIds->count() !== count(array_unique($data['question_ids']))) {
                throw ValidationException::withMessages([
                    'question_ids' => 'Selected class, subject এবং oddhay / chapter অনুযায়ী question select করুন।',
                ]);
            }

        }

        $exam->update([
            'title' => $data['title'],
            'academic_class_id' => $academicClass->id,
            'subject_id' => $subject->id,
            'chapter_id' => $chapter->id,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'duration_minutes' => $data['duration_minutes'],
            'first_prize' => $data['first_prize'] ?? null,
            'second_prize' => $data['second_prize'] ?? null,
            'third_prize' => $data['third_prize'] ?? null,
        ]);

        $exam->questions()->sync($questionIds);

        return back()->with('status', 'Exam updated.');
    }

    private function randomQuestionIds(array $data, AcademicClass $academicClass, Subject $subject, Chapter $chapter): Collection
    {
        $questionIds = Question::query()
            ->where('academic_class_id', $academicClass->id)
            ->where('subject_id', $subject->id)
            ->where('chapter_id', $chapter->id)
            ->inRandomOrder()
            ->limit((int) $data['random_question_count'])
            ->pluck('id');

        if ($questionIds->count() !== (int) $data['random_question_count']) {
            throw ValidationException::withMessages([
                'random_question_count' => 'Selected class, subject and oddhay / chapter e eto question available nei.',
            ]);
        }

        return $questionIds;
    }

    private function orderExamData(Builder $query, string $column, string $direction): Builder
    {
        return match ($column) {
            'academic_class' => $query->orderBy(
                AcademicClass::select('name')->whereColumn('academic_classes.id', 'exams.academic_class_id'),
                $direction
            ),
            'subject' => $query->orderBy(
                Subject::select('name')->whereColumn('subjects.id', 'exams.subject_id'),
                $direction
            ),
            'chapter' => $query->orderBy(
                Chapter::select('name')->whereColumn('chapters.id', 'exams.chapter_id'),
                $direction
            ),
            'duration_minutes', 'starts_at', 'questions_count' => $query->orderBy($column, $direction),
            default => $query->orderBy('title', $direction),
        };
    }

    private function scheduleHtml(Exam $exam): string
    {
        if (! $exam->starts_at || ! $exam->ends_at) {
            return '<span class="muted">Not scheduled</span>';
        }

        return '<strong>' . e($exam->starts_at->format('M d, Y')) . '</strong><br>'
            . '<span class="muted">to ' . e($exam->ends_at->format('M d, Y')) . '</span>';
    }

    private function offerHtml(Exam $exam): string
    {
        if (! $exam->hasPrizes()) {
            return '<span class="muted">No offer</span>';
        }

        return '<span class="muted">'
            . '1st: ' . e($exam->first_prize ?: '-') . '<br>'
            . '2nd: ' . e($exam->second_prize ?: '-') . '<br>'
            . '3rd: ' . e($exam->third_prize ?: '-')
            . '</span>';
    }

    private function actionsHtml(Exam $exam): string
    {
        return view('admin.exams.partials.actions', compact('exam'))->render();
    }
}

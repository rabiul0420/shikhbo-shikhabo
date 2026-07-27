@php
    $limit = $limit ?? null;
    $totalExams = $classExams->count();
    $visibleExams = $limit ? $classExams->take($limit) : $classExams;
    $academicClass = $academicClass ?? $classExams->first()?->academicClass;
    $showViewAll = $limit && $totalExams > $limit && $academicClass;
@endphp
<section class="class-section" aria-labelledby="{{ $status }}-class-{{ \Illuminate\Support\Str::slug($className) }}">
    <div class="class-section-head">
        <div class="class-title">
            <span class="class-mark">{{ \Illuminate\Support\Str::of($className)->substr(0, 1)->upper() }}</span>
            <div>
                <h2 id="{{ $status }}-class-{{ \Illuminate\Support\Str::slug($className) }}">{{ $className }}</h2>
                <p class="muted">{{ $classExams->pluck('subject.name')->filter()->unique()->implode(', ') }}</p>
            </div>
        </div>
        <div class="class-section-actions">
            <span class="pill class-count">{{ __('site.exam.exam_count', ['count' => $totalExams]) }}</span>
            @if ($showViewAll)
                <a class="button class-view-all" href="{{ route('classes.exams', $academicClass->slug) }}">
                    {{ __('site.exam.view_all_class', ['class' => $className]) }}
                </a>
            @endif
        </div>
    </div>

    <div class="exam-card-grid">
        @foreach ($visibleExams as $exam)
            @include('partials.exam-card', ['exam' => $exam, 'status' => $status])
        @endforeach
    </div>
</section>

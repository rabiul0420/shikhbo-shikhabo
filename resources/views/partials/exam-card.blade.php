@php
    $existingAttempt = auth()->check() && ! auth()->user()->is_admin
        ? $exam->attempts->first()
        : null;
    $statusLabel = __('site.home.status_' . $status);
@endphp
<article class="exam-card">
    <div class="exam-card-head">
        <span class="eyebrow">{{ $exam->subject->name }}</span>
        <h3>{{ $exam->title }}</h3>
    </div>

    <div class="exam-meta">
        <div class="exam-meta-item">
            <span>{{ __('site.exam.chapter') }}</span>
            <strong>{{ $exam->chapter->display_name }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>{{ __('site.exam.questions') }}</span>
            <strong>{{ $exam->questions->count() }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>{{ __('site.exam.starts') }}</span>
            <strong>{{ $exam->starts_at ? $exam->starts_at->format('M d, Y') : __('site.exam.not_scheduled') }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>{{ __('site.exam.deadline') }}</span>
            <strong>{{ $exam->ends_at ? $exam->ends_at->format('M d, Y') : __('site.exam.not_scheduled') }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>{{ __('site.exam.duration') }}</span>
            <strong>{{ $exam->duration_minutes ? __('site.exam.minutes', ['count' => $exam->duration_minutes]) : '-' }}</strong>
        </div>
        @if ($exam->hasPrizes())
            <div class="exam-meta-item offer-tooltip" tabindex="0">
                <span>{{ __('site.exam.offer') }}</span>
                <strong>{{ __('site.exam.top_gifts') }}</strong>
                <div class="offer-tooltip-popover" role="tooltip">
                    @if ($exam->first_prize)
                        <div class="offer-tooltip-row">
                            <span>{{ app()->getLocale() === 'bn' ? '১ম' : '1st' }}</span>
                            <strong>{{ $exam->first_prize }}</strong>
                        </div>
                    @endif
                    @if ($exam->second_prize)
                        <div class="offer-tooltip-row">
                            <span>{{ app()->getLocale() === 'bn' ? '২য়' : '2nd' }}</span>
                            <strong>{{ $exam->second_prize }}</strong>
                        </div>
                    @endif
                    @if ($exam->third_prize)
                        <div class="offer-tooltip-row">
                            <span>{{ app()->getLocale() === 'bn' ? '৩য়' : '3rd' }}</span>
                            <strong>{{ $exam->third_prize }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="exam-card-actions">
        <span class="pill status-{{ $status }}">{{ $statusLabel }}</span>
        @if ($existingAttempt)
            <span class="pill status-expired">{{ __('site.exam.already_taken') }}</span>
            <a class="button secondary" href="{{ route('exam-attempts.result', $existingAttempt) }}">{{ __('site.exam.view_result') }}</a>
        @elseif (! auth()->check())
            <a class="button secondary" href="{{ route('exams.show', $exam->slug) }}">{{ __('site.exam.view_details') }}</a>
            <a class="button" href="{{ route('login', ['redirect_to' => route('exams.show', $exam->slug, false)]) }}">{{ __('site.exam.login_to_start') }}</a>
        @elseif ($status === 'running')
            <a class="button" href="{{ route('exams.show', $exam->slug) }}">{{ __('site.exam.start_exam') }}</a>
        @elseif ($status === 'upcoming')
            <span class="pill status-upcoming">{{ __('site.exam.not_started') }}</span>
            <a class="button secondary" href="{{ route('exams.show', $exam->slug) }}">{{ __('site.exam.view_details') }}</a>
        @else
            <a class="button" href="{{ route('exams.show', $exam->slug) }}">{{ __('site.exam.view_questions') }}</a>
        @endif
    </div>
</article>

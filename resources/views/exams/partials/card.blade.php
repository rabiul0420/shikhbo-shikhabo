<article class="exam-card">
    <div class="exam-card-head">
        <span class="eyebrow">{{ $exam->subject->name }}</span>
        <h3>{{ $exam->title }}</h3>
    </div>

    <div class="exam-meta">
        <div class="exam-meta-item">
            <span>Chapter</span>
            <strong>{{ $exam->chapter->display_name }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>Questions</span>
            <strong>{{ $exam->questions->count() }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>Starts</span>
            <strong>{{ $exam->starts_at ? $exam->starts_at->format('M d, Y') : 'Not scheduled' }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>Deadline</span>
            <strong>{{ $exam->ends_at ? $exam->ends_at->format('M d, Y') : 'Not scheduled' }}</strong>
        </div>
        <div class="exam-meta-item">
            <span>Duration</span>
            <strong>{{ $exam->duration_minutes ? $exam->duration_minutes . ' mins' : '-' }}</strong>
        </div>
        @if ($exam->hasPrizes())
            <div class="exam-meta-item offer-tooltip" tabindex="0">
                <span>Offer</span>
                <strong>Top 3 gifts</strong>
                <div class="offer-tooltip-popover" role="tooltip">
                    @if ($exam->first_prize)
                        <div class="offer-tooltip-row">
                            <span>1st</span>
                            <strong>{{ $exam->first_prize }}</strong>
                        </div>
                    @endif
                    @if ($exam->second_prize)
                        <div class="offer-tooltip-row">
                            <span>2nd</span>
                            <strong>{{ $exam->second_prize }}</strong>
                        </div>
                    @endif
                    @if ($exam->third_prize)
                        <div class="offer-tooltip-row">
                            <span>3rd</span>
                            <strong>{{ $exam->third_prize }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="exam-card-actions">
        <span class="pill status-{{ $status }}">{{ ucfirst($status) }}</span>
        @if ($existingAttempt)
            <span class="pill status-expired">Already participated</span>
            <a class="button secondary" href="{{ route('exam-attempts.result', $existingAttempt) }}">View result</a>
        @elseif (! auth()->check())
            <a class="button secondary" href="{{ route('exams.show', $exam) }}">View details</a>
            <a class="button" href="{{ route('login', ['redirect_to' => route('exams.show', $exam, false)]) }}">Login to start</a>
        @elseif ($status === 'running')
            <a class="button" href="{{ route('exams.show', $exam) }}">Start exam</a>
        @elseif ($status === 'upcoming')
            <span class="pill status-upcoming">Not started</span>
            <a class="button secondary" href="{{ route('exams.show', $exam) }}">View details</a>
        @else
            <a class="button" href="{{ route('exams.show', $exam) }}">View questions</a>
        @endif
    </div>
</article>

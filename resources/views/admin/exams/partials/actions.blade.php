<div class="table-actions">
    <a class="button secondary small" href="{{ route('admin.exams.results', $exam) }}">Result</a>
    <button
        class="secondary-action small js-edit-exam"
        type="button"
        data-modal-target="edit-exam"
        data-edit-url="{{ route('admin.exams.edit-data', $exam) }}"
    >Edit</button>
    <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam?')">
        @csrf
        @method('DELETE')
        <button class="danger small" type="submit">Delete</button>
    </form>
</div>

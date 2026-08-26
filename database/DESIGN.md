# Bd ModelTest Database Design

Bd ModelTest uses MySQL, Laravel migrations, and Eloquent models for exam questions and attempts.

## Connection

- Driver: MySQL
- Database: `shikhbo_shikhabo`
- Host: `127.0.0.1`
- Port: `3306`
- Username: `shikhbo_user`

## Tables

### users

Default Laravel user table. A user can create quizzes and submit quiz attempts.

### categories

Groups quizzes by topic, subject, or exam type.

- `id`
- `name`
- `slug`
- `description`
- `is_active`

### quizzes

Stores each test or exam.

- `id`
- `category_id`
- `created_by`
- `title`
- `slug`
- `description`
- `duration_minutes`
- `pass_mark`
- `is_published`
- `starts_at`
- `ends_at`

### questions

Stores quiz questions.

- `id`
- `quiz_id`
- `question_text`
- `type`: `single_choice`, `multiple_choice`, or `true_false`
- `marks`
- `explanation`
- `sort_order`
- `is_active`

### question_options

Stores answer choices for each question.

- `id`
- `question_id`
- `option_text`
- `is_correct`
- `sort_order`

### quiz_attempts

Stores a user's quiz session and final score.

- `id`
- `quiz_id`
- `user_id`
- `status`: `in_progress`, `submitted`, or `graded`
- `score`
- `total_marks`
- `started_at`
- `submitted_at`

### quiz_attempt_answers

Stores selected options for an attempt. Multiple rows per question are allowed, so multiple-choice questions are supported.

- `id`
- `quiz_attempt_id`
- `question_id`
- `question_option_id`
- `is_correct`
- `marks_awarded`

## Relationships

- Category has many quizzes.
- Quiz belongs to a category and creator.
- Quiz has many questions and attempts.
- Question belongs to a quiz.
- Question has many options.
- QuizAttempt belongs to a quiz and user.
- QuizAttempt has many selected answers.
- QuizAttemptAnswer belongs to an attempt, question, and selected option.

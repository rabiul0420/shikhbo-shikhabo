# Separate admin and student accounts

Authentication now uses `Admin` / `admins` with the `admin` session guard,
and `Student` / `students` with the `web` guard. `User` remains a compatibility
model for student integrations only. Login passwords and original IDs are copied
without rehashing. The two tables may independently contain the same ID or contact.

Admin login and logout are `/admin/login` and `/admin/logout`. Student login and
logout remain `/login` and `/logout` (also available under `/bn`). Admin profile
and password changes use `/admin/profile`. Results opened from the admin panel
use the protected `/admin/results/{attempt}` and `/admin/custom-results/{attempt}`
routes. Student exam participation requires a student session.

`created_by` ownership and `gift_awards.given_by` reference `admins`. Attempt and
custom exam `user_id` columns reference `students`. Historical admin participation
is retained in a separate nullable `admin_id`, never assigned to a student with
the same ID. Existing account data is also retained in `users_legacy` as a recovery
snapshot; the application does not authenticate or write accounts there.

## Deployment

1. Back up the database with the site's normal backup system, or run
   `php scripts/backup-accounts-database.php /path/to/mysqldump`.
   Dumps are stored under private, git-ignored `storage/app/private/backups`.
2. Run `php artisan down`.
3. Run `php artisan migrate --path=database/migrations/2026_09_14_000000_separate_admin_and_student_accounts.php --force`.
4. Immediately run `php scripts/verify-account-separation.php`. This compares
   all copied account fields, including password hashes, against the recovery
   snapshot and checks that legacy account foreign keys were removed.
5. Clear cached configuration and views, then run `php artisan up`.

The targeted migration assumes the preceding role and ownership migrations have
already run. This existing local database has historical schema changes whose
migration records are missing; do not run those older pending migrations blindly.
The session cookie namespace changes to require a fresh login after deployment.
No password reset is required.

Migration rollback is intentionally refused: once independent accounts are created,
IDs can collide and cannot be merged automatically. If migration/deployment fails,
keep maintenance mode enabled and restore the complete pre-deployment database
backup together with the previous application code. MySQL DDL is not transactional.

## Tests

`php artisan test --compact --filter='AccountMigrationTest|AccountSeparationTest|AdminPasswordTest|AdminProfileTest|AdminRoleTest|AcademicOwnershipTest|QuestionFilterTest|QuestionOwnershipTest|ExamCreatorTest'`

Tests cover migration preservation, overlapping IDs and contact details, independent
login/logout, admin password validation, restricted roles, and result access.

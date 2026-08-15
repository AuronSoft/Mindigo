# Exam Phase 13 cutover runbook

Phase 13 migrates the legacy `exams` domain to templates, sessions, candidates and session attempts. Legacy rows are never modified or deleted by migration.

## Safe production sequence

```bash
php artisan migrate --force
php artisan exam:inventory --json
php artisan exam:migrate-legacy --dry-run --json
php artisan exam:migrate-legacy
php artisan exam:migrate-legacy --compare --json
```

Do not continue when `issues` is non-empty or any comparison has `matches: false`.

Start a controlled beta while the platform remains in parallel mode:

```bash
php artisan exam:cutover parallel --beta=TEACHER_ID
php artisan exam:cutover --status
```

The selected teachers and candidates in their migrated sessions are routed to the new workspaces. Other users remain on the legacy flow.

After acceptance and data reconciliation, switch all users to the new module:

```bash
php artisan exam:migrate-legacy --compare --json
php artisan exam:cutover new
php artisan exam:cutover --status
```

In `new` mode, legacy authoring, attempts and grading are read-only. Admin exam reports route to aggregate operational metrics.

## Scoped rollback

Return to parallel mode first, then remove only migrated copies for explicitly selected legacy exams:

```bash
php artisan exam:cutover parallel
php artisan exam:migrate-legacy --rollback --exam=123
```

The original legacy exam remains intact. `--force` is reserved for an approved emergency rollback in a non-interactive environment.

## Acceptance checklist

- Compare exam, session, question, attempt and answer counts.
- Verify classroom assignments and candidate snapshots.
- Open migrated results and manually graded answers.
- Test teacher authoring, scheduling, monitoring, grading and analytics.
- Test student start, autosave, submit, result, appeal and practice-from-mistakes.
- Confirm admin sees operational aggregates but not answers or grade controls.
- Monitor migration issues, application logs, queues and Reverb during beta.

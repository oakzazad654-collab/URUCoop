# CWIE legacy data and normalized workflow

## Current state

The application has two independent CWIE data paths:

- The legacy `cwie_documents` table stores CWIE 01–06 as a document type plus a JSON `data` payload. Its approval state and comments are stored on the legacy document and in `approval_histories`.
- The normalized workflow stores each business record in relational tables: applications and qualification reviews, letter requests and their students, placements, work plans and items, report outlines, and supervision requests, schedules, and results. Shared approvals, uploaded documents, and audit events are stored in `approvals`, `documents`, and `audit_logs`.

There is no automatic conversion or synchronization between these paths. A change made to one path does not update the other.

## Recommended operating rule

Use the normalized workflow as the source of truth for new CWIE activity. Keep existing legacy documents and their histories available as read-only records while the migration is planned. Do not dual-write the same submission to both paths.

Treat `internship_applications` as a separate legacy SG03 form. It is not the normalized CWIE application table and should not be included in CWIE conversion without an explicit field mapping.

## Form mapping for a future migration

| Legacy form | Normalized destination candidates |
| --- | --- |
| CWIE 01 | `coop_applications`, `qualification_reviews` |
| CWIE 02 | `letter_requests`, `letter_request_students` |
| CWIE 03 | `placements`, `company_contacts` |
| CWIE 04 | `work_plans`, `work_plan_items` |
| CWIE 05 | `report_outlines` |
| CWIE 06 | `supervision_requests`, `supervision_schedules`, `supervision_results` |
| Approval and files | `approvals`, `documents`, `audit_logs` |

These are candidate mappings, not a claim that every JSON field has a one-to-one destination. A field-level mapping must be reviewed before importing production records.

## Safe migration sequence

1. Choose a term or date as the cutover point and stop new edits to the legacy path after that point.
2. Export and back up the legacy records, approval histories, and referenced files.
3. Build a field mapping and a dry-run importer. Preserve the original legacy record ID and report records that cannot be mapped without guessing.
4. Reconcile source and destination counts, statuses, approval actors/timestamps, and attachment availability.
5. Obtain business-owner review of the reconciliation report before importing into production.
6. Keep the legacy records available for audit; do not delete them as part of the import.

No data conversion is performed by this note. The current production tables remain unchanged.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## CWIE Database and API

The application now includes relational CWIE tables for roles, academic terms, student/staff profiles, applications, letter requests, placements, work plans, report outlines, supervision, approvals, documents, and audit logs. The migration is additive: existing portal tables and the current CWIE forms remain available. Existing `cwie_documents` history is not automatically converted; normalized records are created through the new authenticated API workflows.

For a new MySQL installation, create the database first, configure `.env` with `DB_DATABASE=cooperative_education`, then run:

```sh
php artisan migrate --seed
```

The supplied SQL script starts by dropping and recreating its database. Do not run it unchanged against a database that contains data. Existing installations should back up their database before applying migrations. The normalized migration keeps the legacy user and company primary keys as foreign-key targets so the current portal remains compatible.

API clients authenticate with `POST /api/auth/login` using `identifier` and `password`; the response contains a bearer token, while only its SHA-256 hash is stored. Use that token for the authenticated `/api` routes. Available workflows cover student profiles, companies, CWIE 01–06, approvals, documents, notifications, dashboard summaries, and JSON/CSV/PDF reports. Document uploads are stored on Laravel's private local disk and use generated filenames.

## Data Isolation

`App\Services\DataAccessService` is the shared authorization/query scope for student-owned and faculty-owned records. Student queries use the authenticated user's student profile; advisor and supervisor queries require a matching `staff.faculty_id` and are narrowed to `staff.program_id` when present. Faculty officers are scoped to their faculty; officers without a faculty assignment, executives, and admins are treated as central operators. Missing faculty assignments fail closed for scoped roles.

Controllers should use the scoped builders rather than querying student tables directly:

```php
$students = $access->students($request->user())->paginate(25);
$applications = $access->applications($request->user())->paginate(25);
```

The student query resolves the logged-in user from the bearer token, then scopes the relationship by owner or by the staff member's faculty/program. The underlying faculty filter is equivalent to:

```sql
SELECT s.*
FROM students AS s
JOIN programs AS p ON p.program_id = s.program_id
WHERE p.faculty_id = :authenticated_staff_faculty_id;
```

For student profile queries the scope is `WHERE students.user_id = :authenticated_user_id`; request body IDs never replace the authenticated identity.

Protected API routes resolve the user from the bearer token. Profile detail/update checks and related-record approval/download checks use the same scope. The isolation tests cover cross-student and cross-faculty attempts, including legacy CWIE records and officer user-management URLs.

The standard seeder includes sample SCI/MGT accounts for testing isolation:

- `advisor.sci@uru.ac.th` and `student.sci@uru.ac.th`
- `advisor.mgt@uru.ac.th` and `student.mgt@uru.ac.th`
- `supervisor.sci@uru.ac.th`, `officer.sci@uru.ac.th`, `executive@uru.ac.th`

These accounts use `URU_COOP_DEMO_PASSWORD` (the local example value is `123456`).

Application dates are stored as Gregorian dates and the app timezone is `Asia/Bangkok`. The visual portal layouts and their existing role colors are unchanged.

## Public News

Guests can browse the public homepage at `/`, the cooperative education guide at `/guide`, and published announcements at `/news` with detail pages at `/news/{id}`. Administrators manage drafts, publication times, and optional images at `/admin/news`, and change the homepage hero image or YouTube video at `/admin/appearance`. The homepage background is served from Laravel's public disk by the application, so it remains visible even if the public storage symlink is missing. News images use Laravel's public disk, so configure the public storage link with `php artisan storage:link` when it is not already present. Apply the additive announcements and website-settings migrations with `php artisan migrate` before using these pages.

## Public Registration and Portals

Visitors can register at `/register` as a student, advisor, university supervisor, company mentor, or company. Student accounts are activated immediately; advisor, supervisor, mentor, and company accounts remain pending until an administrator reviews them at `/admin/registrations`. Company registration requires valid latitude and longitude so approved companies can appear on the student map. The mentor role is distinct from the university supervisor role and can only access placements assigned to the mentor's company contact.

Public cooperative-education information is available at `/about-coop`, downloadable reference forms at `/forms`, selected students at `/selected-students`, activities and video content at `/activities`, and the read-only public board at `/board`. Administrators and executives can view the cooperative-education directory at `/admin/directory` and `/executive/directory`; advisors and supervisors can browse companies in their faculty at `/advisor/companies` and `/supervisor/companies`. Student map access is available at `/student/map`.

## Web Reports

Authenticated students can view their application-status and pending-document reports at `/reports`. Advisors, supervisors, officers, administrators, and executives can access the reports allowed by their role, including placement summaries and supervision schedules. Each report reuses the API report queries and data-access scopes and can be viewed in the browser or downloaded as CSV/PDF.

Administrators manage academic terms at `/admin/academic-terms`. Terms include semester/year, study dates, registration dates, and planned/open/closed status. Existing terms are edited rather than deleted so applications and related records retain their references.

## CWIE Reference Forms

The public forms page (`/forms`) provides the CWIE PDF references and lists the original SK PDF forms from `docs/คู่มือต่างๆ`; these files are streamed through an allowlisted download route and do not need to be copied into the web root. Other supplied handbooks, scoring materials, and SP01/SP02/SP04 resources remain under `public/forms`. Forms marked as older versions and handbook files dated September 2023 retain their original source format and should be confirmed with the Cooperative Education Center before being treated as current policy. CWIE 01's online form includes the study type, completed credits, and planned semester/year fields from the supplied original.

Administrators can open `/admin/sk-positions` to calibrate every defined field and repeating-table column for SK-01 through SK-15, including SK-12.1. The editor previews all pages of the source PDFs and stores positions by form; these saved layouts are calibration data only and do not change student entry screens or generated PDFs.

## URU Coop-IS Notifications

The notification workflow stores in-app notifications in the `notifications` table and can queue email and LINE Messaging API delivery.

### SMTP

Set the organization's SMTP values in `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.example
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifications@uru.ac.th
MAIL_FROM_NAME="URU Coop-IS"
```

### LINE Messaging API

Create a LINE Messaging API channel and configure these values in `.env` (never commit the access token or secret):

```dotenv
LINE_CHANNEL_ACCESS_TOKEN=
LINE_CHANNEL_SECRET=
LINE_BOT_BASIC_ID=
LINE_PUSH_ENDPOINT=https://api.line.me/v2/bot/message/push
```

Set the channel webhook URL to `https://your-domain.example/api/line/webhook`, enable webhook delivery in LINE Developers, and use the channel secret for request signature verification. Users sign in, open **การตั้งค่าการแจ้งเตือน**, generate a short-lived binding code, add/open the official account, then send that code to the bot. The webhook stores the LINE user ID and consumes the code once.

After changing `.env`, refresh cached configuration with `php artisan config:clear`. The `LINE_CHANNEL_ACCESS_TOKEN` is shared server configuration; per-user LINE IDs and hashed, expiring binding codes are stored in the database.

### Queue Worker

Notifications implement Laravel's queued notification contract. The default `sync` driver is useful for local development. For asynchronous delivery, set `QUEUE_CONNECTION=database`, then run:

```sh
php artisan queue:work --tries=3
```

The project includes a `jobs` table migration. In-app notification read state is managed from the bell dropdown or the notifications inbox.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

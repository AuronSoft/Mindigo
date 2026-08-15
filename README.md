<p align="center">
  <img src="screenshots/logo.png" style="max-width: 200px; height: auto;" alt="Mindigo LMS">
</p>

<h1 align="center">Mindigo LMS</h1>

<p align="center">
  <strong>A Laravel-based learning management system for admins, teachers, and students.</strong><br>
  Manage classrooms, courses, question banks, exams, assignments, results, reports, internal discussions, and live sessions.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/TailwindCSS-4.x-38B2AC?style=flat&logo=tailwindcss" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-6.x-646CFF?style=flat&logo=vite" alt="Vite">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat" alt="License">
</p>

---

## Table Of Contents

- [Overview](#overview)
- [Core Features](#core-features)
- [Demo Screens](#demo-screens)
- [Module Architecture](#module-architecture)
- [Tech Stack](#tech-stack)
- [Local Setup](#local-setup)
- [Production Deployment](#production-deployment)
- [Demo Accounts](#demo-accounts)
- [Development Commands](#development-commands)
- [Development Guidelines](#development-guidelines)
- [License](#license)

---

## Overview

**Mindigo LMS** is a modular learning management system built with Laravel. The application is organized around three main user experiences:

- **Admin** operates the platform, users, permissions, system health, support, audit logs, and aggregate operational analytics. Admin does not approve exams or alter academic results.
- **Teachers and tutors** own classrooms, courses, question banks, official exams, assignments, grading, reports, announcements, discussions, and live sessions.
- **Students** join classrooms, take assigned exams, create separate practice sets, submit assignments, track schedules and progress, view released results, write notes, and join discussions or live sessions.

The goal of the project is to provide a clear, extensible LMS foundation with consistent UI patterns and practical workflows for online learning.

---

## Core Features

### Administration

- Authentication, password recovery, OTP, and Mindigo ID flows.
- User management, roles, permissions, and account status control.
- Subject, topic, classroom, and classroom member management.
- Operational monitoring for exam traffic, migration/cutover health, queues, storage, and aggregate reporting.
- No academic approval workflow and no access to learner answers or grade editing.
- System settings, profile management, support tickets, and audit logs.

### Teacher Workspace

- Teacher dashboard focused on active classes, students, grading, reminders, schedules, and performance.
- Classroom management with schedules, attendance, and student rosters.
- Course management with chapters and lessons.
- Teacher-owned exam templates, sessions, assignments, candidates, monitoring, grading, result release, analytics, import, and scanning workflows.
- Assignment creation, submission review, grading, and feedback.
- Result review by exam, by student, and by attempt.
- Reports for classes, students, exams, and learning progress.
- Class announcements.
- Internal class discussions with a chat-style interface, images, files, and group information.
- Live session creation and management.

### Student Workspace

- Student dashboard with assigned work, schedule, progress, and recent activity.
- Classroom overview and related learning content.
- Exam taking with autosave, realtime monitoring, configurable proctoring signals, released-result review, and attempt history.
- Assignment receiving and submission through files or text.
- Student-owned practice sets separated from official exams and gradebooks.
- Schedule, learning progress, and leaderboard pages.
- Personal notebook.
- Class discussions and live session participation.

---

## Demo Screens

<p align="center">
  <img src="screenshots/dashboard.png" width="900" alt="Mindigo dashboard demo">
  <br><em>Mindigo dashboard overview.</em>
</p>

<table>
  <tr>
    <td width="50%">
      <img src="screenshots/teacher-dashboard.png" alt="Teacher dashboard">
      <br><strong>Teacher Dashboard</strong>
    </td>
    <td width="50%">
      <img src="screenshots/teacher-exams.png" alt="Teacher exam management">
      <br><strong>Exam Management</strong>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="screenshots/teacher-assignments.png" alt="Assignments and submissions">
      <br><strong>Assignments & Submissions</strong>
    </td>
    <td width="50%">
      <img src="screenshots/teacher-discussions.png" alt="Class discussion chat">
      <br><strong>Class Discussions</strong>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="screenshots/teacher-reports.png" alt="Teacher reports">
      <br><strong>Teacher Reports</strong>
    </td>
    <td width="50%">
      <img src="screenshots/student-live-sessions.png" alt="Student live sessions">
      <br><strong>Student Live Sessions</strong>
    </td>
  </tr>
</table>

<p align="center">
  <video src="screenshots/mindigo.mp4" width="900" controls></video>
  <br><em>Mindigo workflow demo video.</em>
</p>

---

## Module Architecture

Mindigo is organized as internal Composer path packages inside the `packages/` directory.

### `packages/Mindigo`

| Module | Purpose |
| ------ | ------- |
| `Auth` | Login, OTP, Mindigo ID, role middleware |
| `Core` | Public homepage and public layout |
| `Dashboard` | Shared dashboard shell, sidebar, search, and layout |
| `UserManagement` | User account management |
| `RolePermission` | Roles and permissions |
| `SystemSetting` | System configuration |
| `AuditLog` | Activity and operation logs |
| `SubjectManagement` | Subjects and topics |
| `ClassroomManagement` | Platform-level classroom management |
| `QuestionBank` | Question bank management |
| `ExamManagement` | Exam templates, sessions, candidates, attempts, proctoring, monitoring, grading, analytics, migration, and cutover |
| `Report` | Role-scoped learning reports and aggregate operational reports |
| `SupportManagement` | Support tickets |
| `Profile` | Profile, security, and notification preferences |
| `BlogManagement` | News and blog content |

### `packages/Teacher`

| Module | Purpose |
| ------ | ------- |
| `TeacherDashboard` | Teacher dashboard |
| `TeacherClassroom` | Classrooms, schedules, and attendance |
| `TeacherCourse` | Courses, chapters, and lessons |
| `TeacherQuestion` | Teacher question management |
| `TeacherExam` | Teacher exam management |
| `TeacherAssignment` | Assignments and submissions |
| `TeacherResult` | Results, grading, and attempt review |
| `TeacherAnnouncement` | Class announcements |
| `TeacherDiscussion` | Internal class discussions |
| `TeacherLiveSession` | Live sessions |

### `packages/Students`

| Module | Purpose |
| ------ | ------- |
| `StudentDashboard` | Student dashboard |
| `StudentClassroom` | Student classrooms |
| `StudentExam` | Exam taking |
| `StudentAssignment` | Assigned work and submissions |
| `StudentPractice` | Practice sessions |
| `StudentSchedule` | Learning schedule |
| `StudentProgress` | Learning progress |
| `StudentHistory` | Attempt and activity history |
| `StudentLeaderboard` | Leaderboard |
| `StudentNotebook` | Personal notes |
| `StudentDiscussion` | Class discussions |
| `StudentLiveSession` | Live session participation |

---

## Tech Stack

| Layer | Technology |
| ----- | ---------- |
| Backend | Laravel 12, PHP 8.3 |
| Frontend | Blade, Tailwind CSS 4, Vite |
| UI Icons | Blade Heroicons |
| Database | MySQL 8.4 |
| Realtime | Laravel Reverb |
| Cache, sessions, queues | Redis 7.4, Laravel queue workers |
| Local runtime | Docker Compose, Nginx, PHP-FPM, immutable built assets |
| PDF/Export | `barryvdh/laravel-dompdf` |
| Internal packages | Composer path repositories |

---

## Local Setup

Docker Compose is the standard local runtime. Only Docker Desktop (or Docker
Engine with the Compose plugin) is required. PHP, Composer, Node.js, MySQL and
Redis do not need to be installed or started on the host.

### 1. Clone the repository

```bash
git clone https://github.com/scoppy9201/Mindigo.git
cd Mindigo
```

### 2. Start the complete development stack

```powershell
docker compose -f docker-compose.dev.yml build
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml ps
```

The stack starts:

- Nginx and PHP-FPM;
- MySQL and Redis on private Docker networks;
- queue worker and scheduler;
- Laravel Reverb;
- frontend assets built into the application image.

Open the application at <http://127.0.0.1:8080>. Reverb is available at
`127.0.0.1:8082`.

### 3. Seed local data when required

```powershell
docker compose -f docker-compose.dev.yml exec app php artisan db:seed
```

### 4. Develop normally

PHP, Blade, CSS and JavaScript are built into one consistent application image.
After source changes, rebuild the stack so Laravel and Nginx use the same asset
manifest. Do not run `php artisan serve`, `npm run dev`, Redis,
queue workers, the scheduler, or Reverb separately on the host.

Useful checks:

```powershell
docker compose -f docker-compose.dev.yml logs -f app nginx queue reverb vite
docker compose -f docker-compose.dev.yml exec app php artisan migrate:status
curl.exe -I http://127.0.0.1:8080/up
```

See [docs/11-docker-local-development.md](docs/11-docker-local-development.md)
for volumes, ports, rebuilding, testing, troubleshooting, and reset commands.

---

## Production Deployment

Mindigo can be deployed on an Ubuntu virtual machine with Docker Compose. This setup is suitable for demo, internal testing, and production-like deployment when the server is behind NAT and does not have a public IP.

For the full CI/CD webhook guide, see:

```text
docs/6-ci-cd-github-actions.md
```

### 1. Server Requirements

- Ubuntu Server running in VMware, VirtualBox, VPS, or a physical server.
- Docker and Docker Compose plugin.
- Git access to the repository.
- A configured `.env` file or Docker environment variables.
- Optional: ngrok static domain when the VM does not have a public IP.

### 2. Clone And Build On The VM

```bash
cd /home/hung/mindigo
git clone https://github.com/scoppy9201/Mindigo.git
cd Mindigo
docker compose up -d --build
docker compose ps
curl -I http://localhost:8080
```

The default Docker Compose stack exposes:

| Service | URL |
| --- | --- |
| Application | `http://<IP_UBUNTU>:8080` |
| phpMyAdmin | `http://<IP_UBUNTU>:8081` |
| MySQL from host | `127.0.0.1:3307` |

### 3. Prepare Laravel In The Container

Run migrations, clear caches, and seed demo accounts:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan db:seed
```

The production Docker image installs Composer dependencies with `--no-dev`. The database factories are therefore written without depending on `fakerphp/faker`, so seeding still works in a production image.

### 4. CI/CD With GitHub Actions And Webhook

The deployment workflow is:

```text
push main -> GitHub Actions -> build/test -> POST webhook -> ngrok -> Ubuntu VM -> deploy.sh
```

Create these GitHub repository secrets:

| Secret | Example |
| --- | --- |
| `WEBHOOK_URL` | `https://your-ngrok-domain.ngrok-free.dev/deploy` |
| `WEBHOOK_SECRET` | `mindigo-secret` |

On the Ubuntu VM, copy the webhook files:

```bash
mkdir -p ~/webhook
cp /home/hung/mindigo/Mindigo/deploy/webhook/server.py ~/webhook/server.py
cp /home/hung/mindigo/Mindigo/deploy/webhook/deploy.sh ~/webhook/deploy.sh
chmod +x ~/webhook/server.py ~/webhook/deploy.sh
```

Run the webhook manually for a quick test:

```bash
cd ~/webhook
WEBHOOK_SECRET=mindigo-secret python3 server.py
```

Expose it with ngrok:

```bash
ngrok http 9000
```

For a static ngrok domain:

```bash
ngrok http --domain=your-ngrok-domain.ngrok-free.dev 9000
```

When GitHub calls the webhook successfully, the server runs:

```bash
git fetch origin main
git checkout main
git pull --ff-only origin main
DOCKER_BUILDKIT=1 docker compose build app
docker compose up -d --remove-orphans
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear
```

### 5. Keep Webhook And Ngrok Running

For long-running deployment, create systemd services for:

- `mindigo-webhook.service`
- `mindigo-ngrok.service`

Example webhook service:

```ini
[Unit]
Description=Mindigo deploy webhook
After=network.target

[Service]
Type=simple
User=hung
WorkingDirectory=/home/hung/webhook
Environment=WEBHOOK_SECRET=mindigo-secret
Environment=WEBHOOK_PORT=9000
Environment=APP_DIR=/home/hung/mindigo/Mindigo
ExecStart=/usr/bin/python3 /home/hung/webhook/server.py
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Enable it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable mindigo-webhook
sudo systemctl start mindigo-webhook
sudo systemctl status mindigo-webhook
```

### 6. Moving From VM To A Real Server

The same Docker deployment can be moved to a real VPS or physical server. Replace the VM/ngrok parts with normal public networking:

- Point the domain DNS record to the server public IP.
- Put Nginx, Apache reverse proxy, Caddy, Cloudflare Tunnel, or a load balancer in front of `http://127.0.0.1:8080`.
- Enable HTTPS with Let's Encrypt or the platform certificate manager.
- Set `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL=https://your-domain.com`.
- Use strong database passwords and application secrets.
- Do not expose phpMyAdmin publicly; remove it or bind it to localhost/VPN only.
- Keep `storage_data` and `db_data` Docker volumes backed up.
- Run `php artisan migrate --force` during deploy, but run `db:seed` only when demo data is needed.

Useful production checks:

```bash
docker compose ps
docker compose logs --tail=100 app
docker compose exec app php artisan about
curl -I http://localhost:8080
```

CI/CD verification: update this section when testing the GitHub Actions deployment workflow.

---

## Demo Accounts

The database seeder creates fixed accounts for quick testing:

| Role | Email | Password |
| ---- | ----- | -------- |
| Admin | `admin@mindigo.com` | `123456` |
| Teacher | `teacher@mindigo.com` | `123456` |
| Student | `student@mindigo.com` | `123456` |

The seeder also creates additional users with factories for list, permission, and classroom testing.

---

## Development Commands

Run project tooling inside the application container:

```powershell
# Full test suite
docker compose -f docker-compose.dev.yml exec app composer test

# Laravel Pint
docker compose -f docker-compose.dev.yml exec app ./vendor/bin/pint --test

# Clear application caches
docker compose -f docker-compose.dev.yml exec app php artisan optimize:clear

# Refresh Composer autoloading
docker compose -f docker-compose.dev.yml exec app composer dump-autoload

# Inspect all services
docker compose -f docker-compose.dev.yml ps
```

Rebuild the PHP image only after changing its Dockerfile, entrypoint, or PHP
extensions:

```powershell
docker compose -f docker-compose.dev.yml build app
docker compose -f docker-compose.dev.yml up -d --force-recreate app queue scheduler reverb
```

---

## Development Guidelines

- Keep module boundaries clear across `Mindigo`, `Teacher`, and `Students`.
- Use Blade, Tailwind, and existing shared components/icons.
- Put user-facing text in `resources/lang/vi/app.php` and `resources/lang/en/app.php`.
- Define module web routes in `src/routes/web.php`.
- Keep controllers thin and place core business logic in services.
- Follow the existing color system: system green, slate, amber, violet, and rose as accents.
- When adding a new package module, update Composer path repositories, PSR-4 autoloading, and service provider registration if auto-discovery is not available.

---

## License

Mindigo LMS is released under the [MIT License](LICENSE).

---

<p align="center">
  Developed by <strong>Auronsoft</strong><br>
  Mindigo LMS
</p>

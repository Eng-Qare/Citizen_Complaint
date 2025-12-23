You are a senior PHP developer helping me build a City Complaint Management System.

MAIN GOAL:
Build a SIMPLE, CLEAN system using PURE PHP + MySQLi.
There must be NO models, NO MVC, and NO frameworks.

STRICT RULES (MANDATORY):
- DO NOT use Laravel
- DO NOT use any MVC framework
- DO NOT create models
- DO NOT use ORM
- Use PURE PHP only
- Use MySQL with mysqli
- Use HTML forms for web pages
- Use JSON for APIs
- Use PHP sessions
- Focus on folder structure and database correctness

ROLES ONLY (NO PERMISSIONS SYSTEM):
Use ONLY roles from the roles table.
Roles:
- Admin
- Staff
- Citizen

Role checks must use:
$_SESSION['role_id']

PROJECT STRUCTURE:
Follow the defined folder structure exactly.
Each PHP file handles:
- Input
- SQL queries
- Output (HTML or JSON)

DATABASE RULES:
- Use the provided schema exactly
- Do NOT rename tables or columns
- Write SQL directly in PHP files
- Use mysqli prepared statements
- Passwords must use password_hash() and password_verify()

AUTHENTICATION:
- Web login uses sessions
- API authentication uses session or simple token
- Store in session: user_id, role_id, full_name
- Protect all pages except login.php

CORE FUNCTIONALITY:

1) Web Authentication
Files:
- public/login.php
- public/logout.php
- core/auth.php

2) API Authentication
File:
- api/auth.php
Return JSON login status.

3) Dashboard
File:
- public/dashboard.php
Show different data based on role.

4) Complaint Submission
Web:
- public/complaint-submit.php

API:
- api/complaints.php (POST complaint)

Insert directly into complaints table using mysqli.
Initial status must be "Submitted".

5) Complaint Tracking & Management
Web:
- public/complaint-track.php
- public/complaint-view.php

API:
- api/complaints.php (GET / UPDATE)

Update status and log changes in complaint_events table.

6) User Management (Admin)
Web:
- public/user-add.php
- public/user-list.php

API:
- api/users.php

7) Services (Read-only)
API:
- api/services.php

PAGE STRUCTURE STANDARD:
Each PHP file must follow this order:
1) Include core/auth.php (if protected)
2) Include config/database.php
3) Validate input
4) Execute mysqli queries
5) Output HTML or JSON
6) Close DB connection if needed

API RULES:
- Output JSON only
- Set Content-Type: application/json
- No HTML in API files
- No models, no shared data layer

IMPLEMENTATION ORDER:
1) sql/schema.sql
2) config/database.php
3) core/session.php
4) core/auth.php
5) public/login.php
6) api/auth.php
7) public/dashboard.php
8) public/complaint-submit.php
9) api/complaints.php
10) public/complaint-track.php
11) public/complaint-view.php
12) api/users.php
13) api/services.php

OPTIONAL FUTURE TASKS:
- Cron jobs
- Advanced analytics
- AI sentiment analysis

FINAL EXPECTATION:
A clean PURE PHP project using MySQLi, no models, no MVC, role-based access, working web pages and APIs, and correct use of the database schema.
city-complaint-system/
│
├── public/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── complaint-submit.php
│   ├── complaint-view.php
│   ├── complaint-track.php
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── api/
│   ├── auth.php
│   ├── complaints.php
│   ├── users.php
│   └── services.php
│
├── config/
│   ├── database.php
│   ├── app.php
│   └── security.php
│
├── core/
│   ├── auth.php
│   ├── session.php
│   ├── uploader.php
│   └── logger.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── sidebar.php
│
├── uploads/
│   └── complaints/
│
└── sql/
    └── schema.sql


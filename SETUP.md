# Setup and Quick Start

1) Import the database schema (run on the DB host or from your machine if allowed):

```bash
mysql -h 148.222.53.74 -u u675357151_CitizeDB_user -p u675357151_CitizeDB < sql.sql
```

2) Create an admin user (recommended via CLI):

```bash
php tools/create_admin.php "Admin Name" admin@example.com "YourSecurePassword"
```

3) Ensure web server points to `c:/xampp3/htdocs` and open:

- http://localhost/Citizen_Complaint/public/login.php

4) Uploads and logs:

- Uploads folder: `uploads/complaints` (already created)
- Logs directory: `logs/app.log` will be created on first log

Notes:
- The project uses the schema in `sql.sql`. Column names and table names must not be changed.
- Admin role is `role_id = 1` per `sql.sql` insertion order.

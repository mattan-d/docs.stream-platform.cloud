# CLI Scripts

Documentation for VideoTube command-line scripts.

---

## create_admin_user — Create an admin user

Creates a new user with full admin privileges (access to the entire admin panel).

### Requirements

- PHP installed
- Run from the project root directory (or use the full path to `cli/create_admin_user.php`)
- `includes/config.php` and database access configured

### How to run

**From the project root:**

```bash
php cli/create_admin_user.php
```

Or with full path:

```bash
php /path/to/VideoTube/cli/create_admin_user.php
```

### Usage with arguments (command line)

You can pass all data in a single command:

```bash
php cli/create_admin_user.php --username=myadmin --email=admin@example.com --password=YourSecret123 --firstname=Admin --lastname=User
```

| Argument       | Required | Description |
|----------------|----------|-------------|
| `--username=`  | Yes      | Username (5–25 characters, must be unique) |
| `--email=`     | Yes      | Email address (must be unique) |
| `--password=`  | No*      | Password (*if omitted, you will be prompted; input is hidden) |
| `--firstname=` | Yes      | First name |
| `--lastname=`  | Yes      | Last name |

### Interactive usage (prompts)

If you do not pass any arguments, the script will prompt for each field:

```bash
php cli/create_admin_user.php
```

The script will show prompts such as:

- `Username (5-25 chars):`
- `Email:`
- `Password:` (input is hidden)
- `Password (confirm):`
- `First name:`
- `Last name:`

### Validation

- **Username:** 5–25 characters, must not already exist
- **Email:** Must not already exist
- **Password:** Must match the confirmation when entered via prompts

On validation error (username/email taken, invalid length, passwords don’t match), the script prints the errors and exits with code 1.

### Permissions for the new user

The new user is automatically granted:

- `isadmin` = 1 in the database
- Admin panel preferences: access to Videos, Users, Account, Analytics, FAQ, LTI

### Success output

On success, the script prints something like:

```
Admin user created successfully.
  ID: 123
  Username: myadmin
  Email: admin@example.com
```

---

*Additional CLI scripts (cron, reset_password, etc.) can be documented here.*

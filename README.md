# Abdul Hamid — Portfolio

My personal portfolio website — showcasing projects, skills, and a way to get in touch.

**Live:** [https://abdulhamid.my-board.org](https://abdulhamid.my-board.org/?i=1)

---

## Stack

- **PHP 7.4+** — server-side rendering, PDO for database access
- **MySQL / MariaDB** — projects, about content, skills, portfolio images
- **Vanilla CSS + JS** — no framework, no build step
- **Amiri font** — bundled locally for reliable Arabic rendering

---

## Features

### Public site
- **Home** — hero, intro, featured projects, skills
- **About** — bio and background
- **Projects** — grid of all projects with images
- **Contact** — message form (saves to database)

### Admin panel (`/admin/`)
- **Dashboard** — overview stats
- **Projects** — add / edit / delete
- **Skills** — add / edit / delete
- **About content** — editable sections
- **Portfolio images** — upload with auto thumbnails
- **Contact messages** — read, mark read, delete
- **Profile** — change name/email
- **Change password** — with current password verification
- **Forgot password** — email token-based reset flow

### Technical
- Password hashing with `password_hash()` (bcrypt)
- CSRF tokens on POST forms
- PDO prepared statements (SQL injection protection)
- Session hardening (HttpOnly, SameSite)
- Responsive design — desktop, tablet, mobile
- Uploaded images validated + thumbnails generated

---

## Project structure

```
portofolio/
├── .gitignore
├── README.md
├── index.php             ← Home
├── about.php
├── projects.php
├── contact.php
├── config/
│   ├── db.php            ← GITIGNORED (real credentials, on server only)
│   └── db.example.php    ← committed template
├── db/
│   └── schema.sql        ← schema + safe seed data (no users, no emails)
├── admin/                ← admin panel (all CRUD pages)
├── includes/             ← shared header, footer, helpers
└── assets/
    ├── css/
    ├── js/
    └── images/
        ├── profile/
        ├── projects/
        └── background/
```

---

## Local setup (XAMPP / LAMP / MAMP)

### Step 1 — Install prerequisites

- **PHP 7.4+** (8.x recommended)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Apache** with `mod_rewrite` enabled (XAMPP has it on by default)

On Ubuntu / Linux Mint:

```bash
sudo apt install apache2 mysql-server php php-mysql libapache2-mod-php -y
```

### Step 2 — Clone the repo into your web root

**XAMPP on Linux:** web root is `/opt/lampp/htdocs/`

```bash
cd /opt/lampp/htdocs
git clone https://github.com/AbdulHamid172/portofolio.git
```

**XAMPP on Windows:** web root is `C:\xampp\htdocs\`

```bash
cd C:\xampp\htdocs
git clone https://github.com/AbdulHamid172/portofolio.git
```

The site will be reachable at `http://localhost/portofolio/`

### Step 3 — Create the database

Open **phpMyAdmin** (`http://localhost/phpmyadmin`) or use MySQL CLI:

```bash
mysql -u root -p
```

Inside MySQL:

```sql
CREATE DATABASE portofolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

Then import the schema:

```bash
cd /opt/lampp/htdocs/portofolio
mysql -u root -p portofolio < db/schema.sql
```

This creates all tables and inserts safe placeholder data (no users, no contact messages, no real emails).

### Step 4 — Create your config file

The real `config/db.php` is **gitignored** — you must create it locally:

```bash
cd /opt/lampp/htdocs/portofolio
cp config/db.example.php config/db.php
nano config/db.php
```

Edit the values:

```php
$host     = 'localhost';
$dbname   = 'portofolio';
$username = 'root';
$password = 'YOUR_MYSQL_PASSWORD';
$port     = '3306';
```

Save (`Ctrl+O`, Enter, `Ctrl+X`).

### Step 5 — Create your admin account

**Option A — via the web UI (recommended):**

Visit `http://localhost/portofolio/admin/register.php` — if the registration page is available, create your account there.

**Option B — if register page is disabled, insert directly:**

Generate a bcrypt hash of your password:

```bash
php -r 'echo password_hash("your_new_password", PASSWORD_BCRYPT), "\n";'
```

Copy the output (a `$2y$10$...` string, ~60 chars) and insert via phpMyAdmin:

```sql
INSERT INTO admin_users (username, password_hash, email)
VALUES (
  'AbdulHamid',
  '$2y$10$PASTE_THE_HASH_HERE',
  'your@email.com'
);
```

### Step 6 — Access the site

- **Public site:** http://localhost/portofolio/
- **Admin panel:** http://localhost/portofolio/admin/login.php
- **phpMyAdmin:** http://localhost/phpmyadmin

Log in with the admin credentials you created in Step 5.

---

## Deployment (InfinityFree)

The live site is hosted on **InfinityFree** at [https://abdulhamid.my-board.org](https://abdulhamid.my-board.org/?i=1).

### How to deploy updates

1. **Make changes locally** and test them
2. **Commit + push to GitHub:**

   ```bash
   git add -A
   git commit -m "feat: <what changed>"
   git push
   ```

3. **Upload changed files to InfinityFree** via FTP or the File Manager in their control panel:
   - https://dash.infinityfree.com/ → your account → **File Manager** or **FTP Accounts**
   - Upload the changed `.php`, `.css`, `.js` files into `/htdocs/`
   - **Never upload `config/db.php`** from your laptop — the server has its own copy with the production database credentials

### When you change the database password

The password is stored in **two places**:
- `config/db.php` **on the live server** (via FTP)
- `config/db.php` **on your laptop** (if you test locally)

Both must match the password set on InfinityFree's MySQL panel. The GitHub repo never sees either — it only has `config/db.example.php` with placeholders.

**Sequence:**
1. Rotate password at https://dash.infinityfree.com/ → MySQL Databases
2. Update `config/db.php` locally
3. Upload `config/db.php` to the live server via FTP
4. Site works again

---

## Security notes

- **`.gitignore`** excludes `config/db.php`, `.env`, and all `db/*.sql` dumps except the sanitized `schema.sql`
- **Passwords** are hashed with bcrypt (`password_hash()`)
- **All SQL queries** use PDO prepared statements — no string concatenation
- **CSRF tokens** on every POST form
- **Session hardening** — HttpOnly, SameSite=Lax, custom session name
- **Error messages** log to the server, never echo database details to the browser
- **Uploaded images** are validated and re-thumbnailed to prevent arbitrary file uploads

---

## Author

**Abdul Hamid** — Information Technology student, Mount Kenya University (Coast Campus)

GitHub: [@AbdulHamid172](https://github.com/AbdulHamid172)

Live portfolio: [https://abdulhamid.my-board.org](https://abdulhamid.my-board.org/?i=1)

---

## License

This project is provided freely for personal and educational use. No warranty.

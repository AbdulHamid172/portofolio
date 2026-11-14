# Abdul Hamid — Portfolio

My personal portfolio website showcasing projects, skills, and contact information.

**Live:** [https://abdulhamid.my-board.org](https://abdulhamid.my-board.org)

---

## Stack

- **PHP 7.4+** — server-side rendering, PDO for database access
- **MySQL / MariaDB** — projects, about content, skills, portfolio images
- **Vanilla CSS + JS** — no framework, no build step
- **Amiri font** — bundled locally for reliable Arabic rendering

---

## Features

- **Public pages:** Home, About, Projects, Contact
- **Admin panel:** Manage projects, about content, skills, portfolio images, contact messages
- **Authentication:** login, logout, password reset (with email token)
- **Image management:** Upload, thumbnail generation, delete
- **Responsive design:** Desktop, tablet, mobile
- **Contact form:** Saves messages to database

---

## Local Setup (XAMPP / LAMP)

```bash
# 1. Clone the repo
git clone https://github.com/AbdulHamid172/portofolio.git
cd portofolio

# 2. Create local config (real credentials — never committed)
cp config/db.example.php config/db.php
# Edit config/db.php with your MySQL credentials

# 3. Import the schema
mysql -u root -p < db/schema.sql

# 4. Create your admin user (register via the admin panel or insert manually)
# Open http://localhost/portofolio/ in your browser.
# Navigate to /admin/register.php (or run a one-off INSERT — see below).

# 5. Access admin panel
# http://localhost/portofolio/admin/login.php

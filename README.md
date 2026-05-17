# Surveycio — Barangay Health & Vendor Management System

**Surveycio** is a comprehensive, web-based portal designed to modernize barangay (local government) operations. It seamlessly integrates household health monitoring, emergency blood donor tracking, and local vendor permit management into a single, secure platform.

---

## 🌟 Core Features

### 1. Advanced Health & Household Profiling
- **Dynamic Household Registration**: Users can register their entire household under a single account.
- **Granular Health Screening**: Tracks individual medical conditions, current medications, surgical history, and pregnancy status for every family member.
- **Dynamic Form Generation**: Forms dynamically expand based on the number of family members, ensuring complete demographic data is captured.

### 2. Emergency Blood Donor Registry
- **Voluntary Consent**: Built-in consent flows allow individual family members to opt into the emergency donor list.
- **Real-time Tracking**: Captures blood types, previous donation history, and contact information.
- **Rapid Response Dashboard**: Admins have access to a dedicated Donor List table with real-time search and filter capabilities (by blood type and eligibility status).

### 3. Vendor Permit Registration & Management
- **Digital Applications**: Local businesses and vendors can submit permit applications online, including uploading required documents (Business Permit, Cedula, Fire Safety Certificate), handled securely via PHP.
- **Multi-Application Support**: Users can submit and track multiple permit applications simultaneously.
- **Admin Approval Workflow**: Admins can review, approve, or reject vendor applications directly from their dashboard, with notifications sent back to the user.

### 4. Live Analytics & Reporting
- **Decoupled API Architecture**: The Reports module uses a modern API-driven approach — a PHP backend serves JSON data to a pure HTML/JS frontend.
- **Visual Dashboards**: Integrates **Chart.js** to render real-time visualizations of blood type distribution, monthly donor registration trends, and vendor approval statuses.

---

## 🏗️ Project Architecture

The codebase follows a clean, MVC-inspired directory structure with strict Separation of Concerns (SoC).

```
Survey - Group 8/
│
├── index.php                   # Root entry redirect
│
├── client/                     # 🖥️ Frontend Views
│   ├── index.html              # Public Landing Page & Auth (Login/Register)
│   ├── admin_dashboard.php     # Admin Control Panel
│   ├── user_dashboard.php      # User Portal & Application List
│   ├── health_monitoring.php   # Health & Household Profiling Form
│   ├── vendor_registration.php # Vendor Permit Application Form
│   ├── user_settings.php       # Profile, Password & Account Settings
│   ├── reports.html            # Analytics Dashboard (API-driven)
│   ├── product-rules.html      # Product Rules & Guidelines
│   ├── sanitation-rules.html   # Sanitation Rules & Guidelines
│   │
│   └── assets/                 # 🎨 Static Assets
│       ├── css/
│       │   ├── style.css       # Landing page & auth styling
│       │   ├── dashboard.css   # Admin/User dashboard styling
│       │   ├── vendor.css      # Vendor registration form styling
│       │   ├── P-rules.css     # Product rules page styling
│       │   └── S-rules.css     # Sanitation rules page styling
│       ├── js/
│       │   ├── vendor.js       # Vendor form logic & file upload handling
│       │   └── reports.js      # Chart.js fetch & render logic
│       └── img/                # Static images & backgrounds
│
└── server/                     # ⚙️ Backend
    │
    ├── config/
    │   └── db.php              # PDO Database Connection
    │
    ├── actions/                # 🧠 PHP Action Handlers (API endpoints)
    │   ├── login.php           # Authenticates user, starts session
    │   ├── logout.php          # Destroys session
    │   ├── register.php        # Creates new user account
    │   ├── reset_password.php  # Handles password reset
    │   ├── change_password.php # Updates password from settings
    │   ├── update_profile.php  # Updates profile info & avatar
    │   ├── submit_health.php   # Saves health records & family members
    │   ├── submit_vendor.php   # Saves vendor application & uploads docs
    │   ├── review_vendor.php   # Admin: approve/reject vendor application
    │   ├── admin_action.php    # Admin: general actions (blood donors, etc.)
    │   ├── delete_vendor_application.php  # User: delete own application
    │   ├── mark_notifs_read.php           # Marks notifications as read
    │   └── api_reports.php     # REST API — returns JSON for reports page
    │
    ├── scripts/                # 🛠️ Database Setup Scripts
    │   ├── schema.sql          # Full database schema (run this first)
    │   ├── setup_db.php        # Initializes/verifies table structure
    │   └── get_users.php       # Dev utility: lists users
    │
    └── uploads/                # 📁 User-uploaded files (avatars & permits)
```

---

## 🔄 System Workflows

### 1. User Authentication
```
index.html
  ├── Register → actions/register.php       → inserts into `users`
  ├── Login    → actions/login.php          → starts session → user_dashboard.php
  └── Logout   → actions/logout.php         → destroys session → index.html
```

### 2. Health Monitoring & Household Registration
```
health_monitoring.php
  └── Submit → actions/submit_health.php
        ├── Saves household data     → `health_records` table
        └── Saves per-member data    → `family_members` table
              └── If donor consent   → auto-populates `donors` table
```

### 3. Blood Donor Registry
```
health_monitoring.php (donor consent per family member)
  └── submit_health.php → inserts into `donors` table
        └── admin_dashboard.php → view / filter / manage donors
```

### 4. Vendor Permit Application
```
user_dashboard.php → vendor_registration.php
  └── Submit → actions/submit_vendor.php
        ├── Uploads permit docs  → server/uploads/
        └── Saves record         → `vendors` table (status: "pending")
              └── admin_dashboard.php → actions/review_vendor.php
                    └── approve / reject → updates status → notifies user
```

### 5. Admin Control Panel
```
admin_dashboard.php (admin role required)
  ├── Vendor review      → actions/review_vendor.php
  ├── Blood donor mgmt   → actions/admin_action.php
  ├── Delete application → actions/delete_vendor_application.php
  └── Reports            → reports.html → actions/api_reports.php (JSON) → Chart.js
```

### 6. User Account Management
```
user_settings.php
  ├── Update profile & avatar → actions/update_profile.php
  ├── Change password         → actions/change_password.php
  └── Reset password          → actions/reset_password.php
```

### 7. Live Reports & Analytics
```
reports.html (pure frontend)
  └── JS fetch → actions/api_reports.php (REST API → JSON)
        └── Chart.js renders:
              ├── Blood type distribution chart
              ├── Monthly donor registration trends
              └── Vendor application approval status
```

---

## 🗄️ Database Schema

The system uses a relational MySQL database (`servicio_db`).

| Table | Description |
|---|---|
| `users` | Authentication, roles (`admin` / `user`), profile avatar path |
| `health_records` | Household info (Zone, Address) linked to a user |
| `family_members` | Per-member demographics, medical conditions, donor consent |
| `donors` | Blood donor registry, auto-populated from family member consent |
| `vendors` | Vendor permit applications, document paths, approval status |

---

## 🚀 Setup & Installation

### Requirements
- PHP 7.4+ and MySQL 8.0+
- Apache or Nginx web server (XAMPP recommended for local development)

### Steps

1. **Database Setup**
   - Create a database named `servicio_db`.
   - Run `server/scripts/schema.sql` to build the full table structure.
   - Optionally run `server/scripts/setup_db.php` to verify column initialization.

2. **Configuration**
   - Open `server/config/db.php` and update `$host`, `$dbname`, `$user`, and `$pass` to match your local MySQL environment.

3. **Permissions**
   - Ensure the `server/uploads/` directory is writable by the web server.

4. **Launch**
   - Start your PHP server (e.g., `php -S localhost:8000` from the project root).
   - Navigate to `http://localhost:8000/client/index.html` to access the portal.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3 (Vanilla), JavaScript (ES6+) |
| **Icons** | Phosphor Icons |
| **Backend** | PHP 8+ with PDO |
| **Database** | MySQL 8 |
| **Data Visualization** | Chart.js |
| **Local Dev Server** | PHP Built-in Server / XAMPP |

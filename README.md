# Surveycio - Barangay Health & Vendor Management System

**Surveycio** is a comprehensive, web-based portal designed to modernize barangay (local government) operations. It seamlessly integrates household health monitoring, emergency blood donor tracking, and local vendor registration into a single, secure platform.

---

## 🌟 Core Features

### 1. Advanced Health & Household Profiling
* **Dynamic Household Registration**: Users can register their entire household under a single account.
* **Granular Health Screening**: Tracks individual medical conditions, current medications, surgical history, and pregnancy status for every specific family member.
* **Granular Data Management**: Forms dynamically generate based on the number of family members, ensuring complete demographic data is captured.

### 2. Emergency Blood Donor Registry
* **Voluntary Consent**: Built-in consent flows allow individual family members to opt into the emergency donor list.
* **Real-time Tracking**: Captures blood types, previous donation history, and contact information.
* **Rapid Response Dashboard**: Admins have access to a dedicated, lightning-fast Donor List table with real-time search and filter capabilities (by blood type and eligibility status).

### 3. Vendor Registration & Management
* **Digital Applications**: Local businesses and vendors can submit their applications online, including uploading necessary permits (handled securely via PHP).
* **Admin Approval Workflow**: Admins can review, approve, or reject vendor applications directly from their dashboard.

### 4. Live Analytics & Reporting
* **Decoupled API Architecture**: The reports module utilizes a modern API-driven approach. A PHP backend securely serves JSON data to a pure HTML/JS frontend.
* **Visual Dashboards**: Integrates **Chart.js** to render real-time visualizations of blood type distribution, monthly donor registration trends, and vendor approval statuses.

---

## 🏗️ Project Architecture

The codebase follows a clean, professional, MVC-inspired directory structure ensuring strict Separation of Concerns (SoC).

```text
Servicio_Project/
│
├── config/                 # ⚙️ Environment Configs
│   └── db.php              # PDO Database Connection
│
├── actions/                # 🧠 Backend Logic & APIs (PHP Only)
│   ├── login.php           
│   ├── register.php        
│   ├── submit_health.php   
│   ├── submit_vendor.php   
│   ├── update_profile.php  
│   ├── admin_action.php    
│   ├── api_reports.php     # REST API for the Reports Dashboard
│   └── logout.php          
│
├── assets/                 # 🎨 Static Files
│   ├── css/
│   │   ├── style.css       # Landing page styling
│   │   └── dashboard.css   # Internal app styling
│   ├── img/
│   │   └── barangay-bg.png 
│   └── js/
│       └── reports.js      # Frontend fetch logic for Chart.js
│
├── uploads/                # 📁 Secure directory for user avatars and permits
│
├── archive/                # 📦 Legacy prototypes and static designs
├── scripts/                # 🛠️ Database setup and testing scripts
│
└── [Root]                  # 🖥️ Frontend Views (HTML/PHP mixed for templating)
    ├── index.html          # Public Landing & Auth 
    ├── admin_dashboard.php # Admin Control Panel
    ├── user_dashboard.php  # User Portal
    ├── health_monitoring.php
    ├── vendor_registration.php
    ├── user_settings.php
    └── reports.html        # Pure HTML view powered by API
```

---

## 🗄️ Database Schema

The system relies on a relational MySQL database (`servicio_db`).

* **`users`**: Manages authentication, roles (`admin` or `user`), and custom profile avatars.
* **`health_records`**: Stores core household information (Zone, Address) linked to a specific user.
* **`family_members`**: Relational table linked to `health_records`. Stores individual demographics, existing medical conditions, and individual donor consent.
* **`donors`**: A specialized registry automatically populated when a family member consents to donate blood. Includes eligibility status and last donation date.
* **`vendors`**: Tracks business applications, document paths, and approval status (`pending`, `approved`, `rejected`).

---

## 🚀 Setup & Installation

1. **Server Requirements**: Apache or Nginx server running PHP 7.4+ and MySQL 8.0+. (XAMPP/MAMP recommended for local development).
2. **Database Setup**:
   * Create a database named `servicio_db`.
   * Run the SQL scripts located in the `scripts/` folder (`schema.sql`, followed by `setup_db.php` and `setup_db2.php` to ensure all columns are initialized).
3. **Configuration**:
   * Open `config/db.php` and verify the `$host`, `$dbname`, `$user`, and `$pass` match your local SQL environment.
4. **Permissions**:
   * Ensure the `/uploads` directory is writable by your web server (`chmod 755 uploads`).
5. **Launch**:
   * Navigate to `http://localhost/Servicio_Project/index.html` to access the portal.

---

## 🛠️ Tech Stack
* **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (ES6+), Phosphor Icons
* **Backend**: PHP (PDO)
* **Database**: MySQL
* **Libraries**: Chart.js (Data Visualization)

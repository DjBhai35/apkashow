# ApkaShow - Premium Cinematic Movie Platform

An ultra-premium, production-ready dark cinematic movie website and media management hub built with **PHP (OOP/PDO) + MySQL + Bootstrap 5 + Vanilla JavaScript/CSS3**. Engineered specifically for standard shared hosting environments (**cPanel, Hostinger, Bluehost, Namecheap, Apache, LiteSpeed, XAMPP, Laragon**) with **zero Node.js or server runtime dependencies**.

* **Production Domain:** [https://apkashow.com](https://apkashow.com)
* **Brand Name:** **ApkaShow**

![ApkaShow Preview](assets/images/og-preview.jpg)

---

## 🌟 Key Features

### 1. Cinematic Dark Aesthetic & UI
* **Neo-Noir Dark Theme:** Deep obsidian background (`#07090e`), frosted glass navigation (`backdrop-filter: blur(20px)`), and red/amber gradient accents.
* **Modern Typography:** Loaded with Google Fonts *Outfit* (headings) and *Plus Jakarta Sans* (body).
* **Responsive Layout:** 100% mobile-first design with smooth poster hover zooms, rating badges, and video streaming modals.
* **Complete Premium Footer:** Curated navigation links, quick genre categories, direct contact channels, and legal copyright.

### 2. Header & Direct WhatsApp Concierge
* Full responsive navigation with quick links:
  * **Home**
  * **Millionaire**
  * **Billionaire**
  * **Mindset**
  * **Business**
  * **Forex**
  * **Dynamic Categories Dropdown** (Action, Thriller, Bollywood, Hindi Dubbed, Comedy, Romantic, etc.)
  * **About Us**
  * **Contact Us**
* **Instant WhatsApp Integration:** Admin controls the WhatsApp number and pre-filled message from the Admin Panel. Changes reflect immediately across top navigation buttons, floating speed-dial buttons, and movie detail inquiry cards.

### 3. Dynamic Homepage
* **Hero Carousel Banner Slider:** Automatic 5-second rotation with manual controls, custom badges (e.g. *Exclusive Premiere*), and direct streaming buttons. Admin can add, edit, reorder, and toggle banners.
* **Category Filter Pills:** Quick horizontal scrolling genre bar.
* **Curated Movie Sections:**
  * Latest Cinema Releases
  * Popular & Trending Movies
  * Motivational & Mindset Masterpieces
  * Business, Forex & Wealth Cinema

### 4. Movie System & 4K Detail Page
* Dedicated SEO-friendly clean detail pages (`/movie/slug` via `.htaccess`).
* Responsive video player wrapper (16:9) supporting YouTube embeds, Vimeo, or direct MP4 streams.
* Complete film metadata: IMDb rating, duration, release year, language, genre, full plot synopsis, and tag chips.
* **Legal Streaming & Downloads:** Clean download links and legal distribution disclaimers.
* Related movies grid based on matching category.

### 5. Multi-Field Search System
* Real database-powered search running across:
  * Movie Title
  * Short & Full Descriptions
  * Category Name
  * Genre
  * Tags
  * Audio Language
* Live search suggestions API dropdown (`/api/search-suggest.php`).
* Clean empty-state handling and pagination.

### 6. Full Real Admin Panel (`/admin`)
A production-ready administrative control center with authentication and CSRF token protection:
* **Dashboard:** Aggregated statistics (total movies, published titles, stream counts, categories, active banners) and latest user inquiries.
* **Movies Manager:** Add, edit, delete movies, toggle draft/published status, upload poster/backdrop images or specify image URLs, configure streaming URLs, and input SEO metadata.
* **Category Manager:** Add, edit, delete categories, manage URL slugs, pick Bootstrap icons, and configure custom category SEO titles/descriptions.
* **Featured Banners Manager:** Add, sort, and edit hero slides for the homepage carousel.
* **System & WhatsApp Settings:** Instant WhatsApp number updates, custom branding text, advertisement slots (header/footer code), About Us & Contact Us text editor, and default SEO tags.
* **User Inquiries:** View and manage messages submitted via the Contact Us form.
* **Staff Users & Secure Password Enforcement:** Create administrator/editor accounts with secure password hashing (`password_hash` with Bcrypt).

### 7. Technical SEO & Schema
* Clean URLs via `.htaccess` rewrites (`/movie/slug` and `/category/slug`).
* Canonical URL implementation pointing strictly to `https://apkashow.com`.
* Automated **Schema.org Movie JSON-LD** structured data on every movie page.
* Dynamic Open Graph (OG) & Twitter card tags for social sharing.
* Auto-generated dynamic **XML Sitemap** (`/sitemap.xml` / `sitemap.xml.php`).
* SEO crawler directives via `robots.txt`.

### 8. Security & Performance
* PDO prepared statements throughout to prevent SQL Injection.
* Output escaping via `e()` helper function to prevent Cross-Site Scripting (XSS).
* Session security (`cookie_httponly`, session regeneration on login).
* Forced password update protection for new administrator accounts.
* CSRF token protection on all administrative and contact forms.
* Secure image file upload validation (checking MIME-types via `finfo`).
* Gzip compression directives and directory listing disabled in `.htaccess`.

---

## 🛠️ Technology Stack
* **Backend:** PHP 7.4 / 8.0 / 8.1 / 8.2 / 8.3 (Native PDO, no Node.js runtime required)
* **Database:** MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+
* **Frontend:** Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Vanilla CSS3 & Modern JavaScript (ES6)
* **Hosting Compatibility:** Apache / LiteSpeed / Nginx / cPanel / Hostinger / XAMPP / Laragon

---

## 🚀 Installation & Deployment Guide

### Option A: Standard Deployment (Hostinger / cPanel / Shared Hosting)

1. **Upload Files:**
   * Upload all project files into your hosting account's web root (`public_html`).
2. **Create MySQL Database:**
   * In cPanel or Hostinger hPanel, navigate to **MySQL Databases** and create a database (e.g. `u123456_apkashow`).
   * Create a database user, assign a password, and grant **ALL PRIVILEGES** to the user on that database.
3. **Configure Database Credentials:**
   * Open `includes/config.php` (or copy `includes/config.example.php` to `includes/config.php`).
   * Update the database constants:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'your_database_name');
     define('DB_USER', 'your_database_user');
     define('DB_PASS', 'your_database_password');
     ```
4. **Initialize Database & Master Admin:**
   * Visit `https://apkashow.com/install.php` in your browser.
   * Click **Initialize Database Schema & Seed Data** to import the curated movie collection.
   * You will then be prompted to define your personal, secure master administrator credentials (username, email, and password min. 8 characters).
   * For optimal security after setup, delete or restrict `install.php`.
5. **Set Folder Permissions:**
   * Ensure `assets/uploads/` (and its subfolders `movies/` and `banners/`) have write permissions (`755` or `775`).

---

### Option B: Local Testing (XAMPP / Laragon / WampServer)

1. Move the project folder into your web directory:
   * **XAMPP:** `C:\xampp\htdocs\apkashow\`
   * **Laragon:** `C:\laragon\www\apkashow\`
2. Open your browser and navigate to:
   * `http://localhost/apkashow/install.php`
3. Execute the setup wizard to create the database and set your admin password.

---

## 📱 WhatsApp Concierge Configuration

To configure the live WhatsApp button:
1. Log in to the Admin Panel (`/admin/`).
2. Navigate to **Site & WhatsApp Settings** (`/admin/settings.php`).
3. Under the **WhatsApp Integration** section, enter your phone number with international country code (e.g., `+1234567890` or `+919876543210`).
4. Enter your custom pre-filled message.
5. Click **Save All Platform Settings**. All frontend WhatsApp buttons and floating icons will immediately connect to this number.

---

## 📁 Project Directory Structure

```
├── .htaccess                  # Apache clean URL rewrites, security and compression
├── .gitignore                 # Git ignore file excluding sensitive configs and caches
├── index.php                  # Homepage with carousel, categories & curated rows
├── movie.php                  # Movie streaming, specs, downloads & detail page
├── category.php               # Dynamic category archive with pagination
├── search.php                 # Real multi-field search engine
├── about.php                  # About us page (admin managed)
├── contact.php                # Contact form & WhatsApp concierge
├── install.php                # Database setup assistant & secure admin provisioner
├── robots.txt                 # Search engine crawler directives
├── sitemap.xml.php            # Dynamic XML sitemap generator
├── database/
│   └── schema.sql             # Full database schema and initial curated seeds
├── api/
│   └── search-suggest.php     # Live AJAX search suggestions
├── includes/
│   ├── config.php             # Active database credentials & environment
│   ├── config.example.php     # Safe configuration template for deployment
│   ├── db.php                 # PDO database singleton connection
│   ├── functions.php          # Security sanitization, CSRF, uploads, helpers
│   ├── header.php             # Cinematic navigation bar with WhatsApp button
│   ├── footer.php             # Cinematic dark footer & floating WhatsApp icon
│   └── movie_card.php         # Reusable movie card component
├── assets/
│   ├── css/
│   └── js/
│   └── uploads/
│       ├── movies/            # Movie posters uploaded via admin
│       └── banners/           # Hero backdrop images uploaded via admin
└── admin/
    ├── header.php             # Admin sidebar & authenticated header
    ├── footer.php             # Admin dashboard footer
    ├── login.php              # Secure login with Bcrypt & CSRF checks
    ├── logout.php             # Admin session destroy
    ├── index.php              # Dashboard analytics & recent messages
    ├── movies.php             # Movie catalog manager & status toggles
    ├── movie_edit.php         # Full movie creator & editor with file uploads
    ├── categories.php         # Category CRUD & icon management
    ├── banners.php            # Hero slider banner manager
    ├── settings.php           # Site branding, WhatsApp number & Ad settings
    ├── messages.php           # User inquiries manager
    └── users.php              # Admin account creation & forced password updates
```

---

## ⚖️ Legal Content Compliance
ApkaShow is built for webmasters streaming authorized promotional trailers, public domain media, licensed video streams, or direct archival content. No deceptive pop-up scripts or forced redirect advertising loops are included.

---

## 📄 License
This project is licensed under the MIT License. Built for high performance on modern PHP hosting platforms.

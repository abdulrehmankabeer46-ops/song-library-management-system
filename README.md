# 🎵 SongVault — Song Library Management System

A **Full Stack CRUD Web Application** for managing a personal music library. Built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript — running on XAMPP local server.

![PHP](https://img.shields.io/badge/PHP-8.0-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat&logo=javascript&logoColor=black)
![XAMPP](https://img.shields.io/badge/XAMPP-Local_Server-FB7A24?style=flat&logo=xampp&logoColor=white)

---

## 👨‍💻 Student Info

| Field | Details |
|---|---|
| **Student ID** | L1F24BSCS0428 |
| **Course** | Advanced Web Development / Full Stack Development |
| **University** | University of Central Punjab (UCP) |
| **Semester** | Semester 4 |
| **Submission Date** | 18th June 2026 |
| **Total Marks** | 50 |

---

## 📋 Project Overview

SongVault is a complete **CRUD (Create, Read, Update, Delete)** web application that allows users to manage a personal song library. The system stores song details like title, artist, genre, album, duration, release year, and star rating in a MySQL database — all accessible through a modern, responsive web interface.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **CSS Framework** | Bootstrap 5.3 |
| **Backend** | PHP 8 (Core PHP) |
| **Database** | MySQL 8 via phpMyAdmin |
| **Local Server** | XAMPP (Apache + MySQL) |
| **API Style** | AJAX (Fetch API) |

---

## ✅ Features

### Core CRUD Operations
- ➕ **Create** — Add new songs via a modal form with full validation
- 👀 **Read** — View all songs in a dynamic, sortable table
- ✏️ **Update** — Edit existing songs with pre-filled form
- 🗑️ **Delete** — Remove songs with a confirmation dialog

### Extra Features
- 🔍 **Live Search** — Search by title, artist, or album (debounced)
- 🎸 **Genre Filter** — Filter songs by genre
- ↕️ **Column Sorting** — Sort by title, artist, year, rating, or plays
- ⭐ **Star Rating** — Rate songs from 1 to 5 stars
- 📊 **Dashboard Stats** — Total songs, artists, genres, plays & avg rating
- 📱 **Responsive UI** — Works on mobile and desktop screens
- 🔔 **Toast Notifications** — Success/error alerts for every action

---

## 📁 Project Structure

```
song_library/
│
├── index.php               # Main UI — layout, modals, stats bar
├── api.php                 # AJAX API endpoint — all CRUD routes
├── database.sql            # MySQL schema + 20 seed songs
├── Documentation.docx      # Full project documentation
│
├── includes/
│   ├── db.php              # MySQLi database connection
│   └── helpers.php         # All CRUD functions + input validation
│
├── css/
│   └── style.css           # Custom dark theme stylesheet
│
└── js/
    └── app.js              # Frontend logic — Fetch API, events, render
```

---

## 🗄️ Database Design

### Tables

| Table | Primary Key | Description |
|---|---|---|
| `songs` | id (INT, AI) | Main table — all song records |
| `artists` | id (INT, AI) | Artist names and countries |
| `genres` | id (INT, AI) | Music genre categories |

### Relationships
- `songs.artist_id` → `artists.id` *(Many-to-One, ON DELETE CASCADE)*
- `songs.genre_id` → `genres.id` *(Many-to-One, ON DELETE RESTRICT)*

### ER Diagram
```
artists          songs                genres
────────         ──────────────────   ──────────
id (PK)   ◄──── artist_id (FK)        id (PK)
name             id (PK)        ────► genre_id (FK)
country          title                name
                 album
                 duration_sec
                 year
                 rating
                 play_count
```

---

## 🚀 How to Run the Project

### Prerequisites
- [XAMPP](https://www.apachefriends.org) installed (Apache + MySQL)

### Step-by-Step Setup

**1. Start XAMPP**
```
Open XAMPP Control Panel → Start Apache → Start MySQL
```

**2. Copy Project Files**
```
Copy song_library/ folder → paste into C:\xampp\htdocs\
```

**3. Setup Database**
```
1. Open browser → http://localhost/phpmyadmin
2. Click "New" → Create database: song_library_db
3. Click "Import" → Select database.sql → Click "Go"
```

**4. Run the App**
```
Open browser → http://localhost/song_library/
```

### Default DB Credentials (`includes/db.php`)
```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = ''           // blank (XAMPP default)
DB_NAME = 'song_library_db'
```

---

## 🔒 Security

- ✅ All queries use **MySQLi Prepared Statements** (SQL injection safe)
- ✅ Input sanitised with `htmlspecialchars()` and `trim()`
- ✅ Server-side validation on all form inputs
- ✅ Proper HTTP response codes (400, 404, 422, 500)

---

## 📸 Screenshots

> *Run the project locally and add screenshots here*

---

## 📄 License

This project is submitted as a university assignment for **Advanced Web Development** at **University of Central Punjab (UCP)**.

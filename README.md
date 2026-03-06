# FUD Security Case Reporting & Management System

A comprehensive web-based system for managing security cases at Federal University Dutse.

## Features

### Public Features
- Anonymous case reporting with tracking code
- Report status tracking
- Responsive Bootstrap design

### CSO Features
- Dashboard with statistics
- Officer management
- Case assignment
- Report analytics
- Category management

### Officer Features
- Case management
- Status updates
- Evidence upload
- Progress tracking

## Installation

1. **Database Setup**
   - Import `security_db.sql` to your MySQL database
   - Update database credentials in `config/database.php`

2. **Web Server Setup**
   - Place files in your web server directory (Apache/Nginx)
   - Ensure PHP 7.4+ with PDO MySQL extension
   - Create `uploads/` directory with write permissions

3. **Default Login**
   - CSO: username `cso`, password `admin123`
   - Officers: Can be created by CSO

## File Structure
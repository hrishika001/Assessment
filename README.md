# Student Management Portal System

## Project Overview
The Student Management Portal is a role-based web application developed using PHP and MySQL.  
It provides a secure platform where an Admin can manage student and teacher accounts, Teachers can handle academic records, and Students can log in to view their information.

The system is designed to support basic university/college portal operations such as user management, results, announcements, assignments, and profile handling.



## Login Credentials

Use the following default accounts for testing:

### Admin Login
- Email: admin@portal.com  
- Password: hrishika123  

### Teacher Login
- Email: sushil@gmail.com  
- Password: 123456 

### Student Login
- Email: kamathrishika101@gmail.com 
- Password: 123456

- Email: shaan@gmail.com 
- Password: 123456

- Email: ram@gmail.com 
- Password: 123456

> These credentials are included as dummy data for demonstration purposes and can be updated in the database.


## Setup Instructions


### Follow these steps to run the project in herald server:###

## Server Requirements
- Herald Server Credentials
- PHP 8.0 or higher
- MySQL / MariaDB Database
- Apache Web Server
- File upload permission enabled (for student photos)


## Deployment Steps

1. **Upload Project Folder into server**
   - Log in to server by using crendentials
   - upload your project folder into server inside public_html/ folder.


2. **Create MySQL Tables in server**
   - Go to public_html and write code mysql -p 
   - Use the defult databse. Mine is NP03CY4S250021
   - Create Tables using mysql queries.
     

3. **Configure Database Connection**
  - Update the database credentials in: db.php
  - use these as Server Credentials
        $DB_HOST = "localhost";
        $DB_NAME = "NP03CY4S250021";
        $DB_USER = "NP03CY4S250021";
        $DB_PASS = "RTnWTjiGKY";

4. **Run your project in the browswe**
   - run the project in any browser using this link : https://student.heraldcollege.edu.np/~NP03CY4S250021/STUDENT-PORTAL/public/index.php
    



### Follow these steps to run the project locally:###

## Server Requirements
- XAMPP / WAMP Server
- PHP 8+
- MySQL Database
- Web Browser (google recommended)



## 2. Installation Steps

1. Extract the project folder into:
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin:http://localhost/phpmyadmin
4. Create a database named: student_portal
5. Import the SQL file provided.
6.  Run the project in browser: http://localhost/Student-Portal/public/index.php





## Features Implemented

### Admin Module
- Secure Admin Login
- Create and manage Teacher accounts
- Create and manage Student accounts
- Upload Student ID photo
- View all users in the system
- Manage announcements, results, assignments, attendance

### Teacher Module
- Teacher Login Authentication
- View announcements
- Upload student academic results
- Manage student assignments and submissions
- Access student lists assigned under teacher

### Student Module
- Student Login Only (no signup access)
- View personal profile and ID card
- View academic results
- View announcements and assignment updates



## Speciality / Highlights

- Role-based Access Control (Admin, Teacher, Student)
- Modern UI design with responsive layout
- Secure password hashing for accounts
- Structured modules for easy navigation
- Student photo-based ID card system



## Limitations

- Email notifications are not implemented
- No online payment or fee system included
- Limited reporting/dashboard analytics
- File upload validation is basic
- System is designed mainly for local deployment (not production-ready)

---

## Future Work

In future improvements, the system can include:

- Online assignment grading system
- Attendance automation with QR scanning
- Email/SMS notification integration
- Student self-service profile updates
- Advanced admin analytics dashboard
- Deployment support for live hosting



## Known Issues

- Some pages may show layout gaps depending on screen size
- Announcement creation permissions may require further refinement
- Token/session errors may occur if browser cache is not cleared
- Upload folders must have correct write permissions



## 
Developed by: **Hrishika Kamat**  
student id : 2548701
Module: Fullstack- 5CS018 (Assessment Project)


   








   



CREATE DATABASE IF NOT EXISTS attendance_db;
USE attendance_db;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100),
    role ENUM('admin','teacher','student')
);
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    user_id INT UNIQUE,
    roll_no VARCHAR(50) NOT NULL,
    class VARCHAR(50) NOT NULL,
    section VARCHAR(10) NOT NULL,
    contact VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE,
    status ENUM('Present','Absent','Late'),
    FOREIGN KEY(student_id) REFERENCES students(id)
);
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(200) NOT NULL,
    address TEXT,
    contact VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create database
CREATE DATABASE IF NOT EXISTS online_book_store;
USE online_book_store;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'reader', 'writer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author_id INT NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    file_path VARCHAR(255),
    is_free BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category_id INT,
    FOREIGN KEY (author_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Updated Purchases table
CREATE TABLE purchases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reader_id INT NOT NULL,
    book_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    phone_number VARCHAR(20), -- For matching via M-Pesa callback
    payment_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(50) NULL,
    mpesa_receipt VARCHAR(50), -- Stores M-Pesa receipt number
    payment_date DATETIME NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reader_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, email, role) 
VALUES ('admin', 'admin123', 'admin@bookstore.com', 'admin');

-- Insert sample writer (password: writer123)
INSERT INTO users (username, password, email, role) 
VALUES ('writer1', 'writer123', 'writer1@bookstore.com', 'writer');

-- Insert sample reader (password: reader123)
INSERT INTO users (username, password, email, role) 
VALUES ('reader1', 'reader123', 'reader1@bookstore.com', 'reader');

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Romance', 'Books about love and relationships'),
('Programming', 'Books about computer programming and software development'),
('Mental Health', 'Books about psychological well-being and mental health'),
('Hadithi', 'Books containing stories and narratives'),
('Thriller', 'Books with intense excitement and suspense'),
('Horror', 'Books designed to scare and unsettle readers'),
('Love', 'Books focusing on romantic relationships'),
('Religion', 'Books about religious beliefs and practices'),
('Geography', 'Books about the physical features of the Earth'),
('History', 'Books about past events and historical periods'),
('Science', 'Books about scientific discoveries and principles'); 
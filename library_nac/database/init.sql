-- Database initialization for CTF Library System
-- This creates the structure with hidden flags throughout

CREATE DATABASE IF NOT EXISTS library_ctf;
USE library_ctf;

-- User table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('user', 'admin', 'librarian') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1
);

-- Books table (FLAG 1 will be here)
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    publisher VARCHAR(100),
    publish_year INT,
    category VARCHAR(50),
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    description TEXT,
    cover_image VARCHAR(255),
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Special archive table (FLAG 6 - requires advanced SQL)
CREATE TABLE archive_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_book_id INT,
    archive_code VARCHAR(50),
    storage_location VARCHAR(100),
    archived_date DATE,
    notes TEXT,
    FOREIGN KEY (original_book_id) REFERENCES books(id)
);

-- Borrowing records
CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Admin logs (FLAG 9 - IDOR vulnerability)
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- System configuration (FLAG 10 - complex query needed)
CREATE TABLE system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE,
    config_value TEXT,
    is_hidden TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reviews table
CREATE TABLE book_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert initial data

-- FLAG 1: Simple query - "What is the ISBN of the oldest book in the mystery section?"
-- Answer: 978-0486284736
INSERT INTO books (title, author, isbn, publisher, publish_year, category, total_copies, available_copies, description) VALUES
('The Murders in the Rue Morgue', 'Edgar Allan Poe', '978-0486284736', 'Dover Publications', 1841, 'Mystery', 3, 2, 'Considered the first modern detective story'),
('Pride and Prejudice', 'Jane Austen', '978-0141439518', 'Penguin Classics', 1813, 'Romance', 5, 4, 'A classic tale of love and society'),
('1984', 'George Orwell', '978-0451524935', 'Signet Classic', 1949, 'Fiction', 4, 3, 'A dystopian social science fiction novel'),
('To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 'Harper Perennial', 1960, 'Fiction', 6, 5, 'A novel about racial injustice'),
('The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'Scribner', 1925, 'Fiction', 4, 4, 'Story of the mysteriously wealthy Jay Gatsby'),
('Moby Dick', 'Herman Melville', '978-0142437247', 'Penguin Classics', 1851, 'Adventure', 2, 2, 'The quest of Ahab for the white whale'),
('Database Systems Fundamentals', 'Dr. Robert Smith', '978-1234567890', 'Tech Press', 2015, 'Technology', 3, 3, 'Comprehensive guide to databases'),
('The Art of War', 'Sun Tzu', '978-1599869773', 'Pax Librorum', 500, 'Philosophy', 5, 5, 'Ancient military treatise'),
('Sherlock Holmes Complete', 'Arthur Conan Doyle', '978-0553328257', 'Bantam Classics', 1887, 'Mystery', 3, 2, 'Complete adventures of Sherlock Holmes'),
('The Cuckoos Calling', 'Robert Galbraith', '978-0316206846', 'Mulholland Books', 2013, 'Mystery', 4, 3, 'A private detective investigates');

-- FLAG 6: Archive location needed - "Where is the book with archive code ARC-1841-POE stored?"
-- Answer: BASEMENT-VAULT-A7
INSERT INTO archive_books (original_book_id, archive_code, storage_location, archived_date, notes) VALUES
(1, 'ARC-1841-POE', 'BASEMENT-VAULT-A7', '2024-01-15', 'Rare first edition copy'),
(6, 'ARC-1851-MEL', 'BASEMENT-VAULT-B3', '2024-02-10', 'Historical collection'),
(8, 'ARC-0500-TZU', 'SPECIAL-COLLECTION-C1', '2024-03-01', 'Ancient philosophy section');

-- Users
-- FLAG 7: Admin bypass - username exists "librarian_admin" 
-- FLAG 8: Password is MD5 hash of "library2024" = 8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918
INSERT INTO users (username, password, email, role, is_active) VALUES
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', 'user', 1),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@example.com', 'user', 1),
('admin', '$2y$10$SbXj3rT8xKz1p7vN8mQz6O8YZ7bQqN6xK/Z9xK8xK9xK0xK1xK2xK3', 'admin@library.local', 'admin', 1),
('librarian_admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 'librarian@library.local', 'librarian', 1),
('test_user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'test@example.com', 'user', 1);

-- Borrowing records
INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, return_date, status) VALUES
(1, 1, '2024-01-10', '2024-01-24', '2024-01-22', 'returned'),
(1, 3, '2024-02-01', '2024-02-15', NULL, 'borrowed'),
(2, 2, '2024-01-15', '2024-01-29', '2024-01-28', 'returned'),
(2, 5, '2024-02-05', '2024-02-19', NULL, 'borrowed'),
(5, 4, '2024-01-20', '2024-02-03', NULL, 'overdue');

-- FLAG 9: Admin log with sensitive info - "What IP did admin use on 2024-01-15 at 14:30?"
-- Answer: 192.168.1.100
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(3, 'user_created', 'Created new user: librarian_admin', '192.168.1.100', '2024-01-15 14:30:00'),
(3, 'book_added', 'Added book: The Murders in the Rue Morgue', '192.168.1.105', '2024-01-15 15:00:00'),
(3, 'config_changed', 'Updated system configuration', '192.168.1.100', '2024-01-16 09:00:00'),
(4, 'book_updated', 'Updated book availability', '10.0.0.50', '2024-02-01 10:30:00');

-- FLAG 10: Hidden config - "What is the master_key value?"
-- Answer: CTF-MASTER-KEY-2024-LIBRARY
INSERT INTO system_config (config_key, config_value, is_hidden) VALUES
('site_name', 'Digital Library System', 0),
('max_borrow_days', '14', 0),
('fine_per_day', '0.50', 0),
('maintenance_mode', '0', 0),
('master_key', 'CTF-MASTER-KEY-2024-LIBRARY', 1),
('backup_location', '/var/backups/library', 1),
('api_secret', 'sk_live_51234567890abcdef', 1);

-- FLAG 11: Complex JOIN query - "What is the email of the user who borrowed the book written by the author of the oldest mystery book?"
-- The oldest mystery book is by Edgar Allan Poe (book_id=1), borrowed by user_id=1 (john_doe)
-- Answer: john@example.com

-- Book reviews
INSERT INTO book_reviews (book_id, user_id, rating, review_text) VALUES
(1, 2, 5, 'Excellent detective story! A must-read classic.'),
(2, 1, 5, 'Beautiful prose and timeless romance.'),
(3, 5, 4, 'Disturbing but important read.'),
(4, 2, 5, 'Powerful story about justice and morality.');

-- Create indexes for performance
CREATE INDEX idx_books_category ON books(category);
CREATE INDEX idx_books_isbn ON books(isbn);
CREATE INDEX idx_borrowings_user ON borrowings(user_id);
CREATE INDEX idx_borrowings_status ON borrowings(status);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_admin_logs_date ON admin_logs(log_date);

-- Grant privileges
GRANT ALL PRIVILEGES ON library_ctf.* TO 'library_user'@'%' IDENTIFIED BY 'library_pass_2024';
FLUSH PRIVILEGES;

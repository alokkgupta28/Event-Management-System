-- Create database
CREATE DATABASE IF NOT EXISTS event_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_management;

-- Users table
CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	email VARCHAR(150) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	role ENUM('user','admin') NOT NULL DEFAULT 'user',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Events table
CREATE TABLE IF NOT EXISTS events (
	id INT AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(150) NOT NULL,
	description TEXT NOT NULL,
	date DATE NOT NULL,
	location VARCHAR(200) NOT NULL,
	image VARCHAR(255) DEFAULT NULL,
	category VARCHAR(100) DEFAULT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_date (date),
	INDEX idx_category (category)
) ENGINE=InnoDB;

-- Registrations table
CREATE TABLE IF NOT EXISTS registrations (
	id INT AUTO_INCREMENT PRIMARY KEY,
	event_id INT NOT NULL,
	user_id INT NOT NULL,
	tickets INT NOT NULL DEFAULT 1,
	timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	UNIQUE KEY uniq_registration (event_id, user_id),
	FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed an admin user (password: admin123 - replace immediately)
INSERT INTO users (name, email, password, role)
VALUES ('Administrator', 'admin@example.com', '$2y$10$U3hXYN6mP7yqI7qT0AxQwO8f7H4b0v0o1pniy0kL0z3m2.gm1Kp1C', 'admin')
ON DUPLICATE KEY UPDATE email=email;

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at)
) ENGINE=InnoDB;

-- Seed sample India events
INSERT INTO events (title, description, date, location, image, category) VALUES
('Tech Summit Mumbai', 'Join India\'s leading tech innovators and startups for a day of talks and networking.', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Mumbai, Maharashtra', NULL, 'Conference'),
('Hyderabad AI Workshop', 'Hands-on workshop covering practical AI/ML projects and deployment tips.', DATE_ADD(CURDATE(), INTERVAL 28 DAY), 'Hyderabad, Telangana', NULL, 'Workshop'),
('Delhi Developer Meetup', 'Community meetup for web and mobile developers with lightning talks.', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'New Delhi, Delhi', NULL, 'Meetup'),
('Goa Startup Retreat', 'A beachside retreat for founders to connect, brainstorm, and relax.', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'Goa', NULL, 'Retreat')
ON DUPLICATE KEY UPDATE title=title;
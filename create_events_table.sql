-- Create the events table for the event planner application
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    attendees JSON, -- Store attendees as JSON array
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Optional: Insert sample events for testing
-- INSERT INTO events (title, category, description, date, time, attendees, user_id) VALUES
-- ('Sample Event', 'Work', 'This is a sample event', '2025-12-01', '10:00:00', '["user1@example.com", "user2@example.com"]', 1);

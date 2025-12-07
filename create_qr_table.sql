-- Create the event_qr_codes table for storing QR code data for attendee tracking
CREATE TABLE event_qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    attendee_email VARCHAR(255) NOT NULL,
    qr_code_url TEXT NOT NULL,
    tracking_code VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used BOOLEAN DEFAULT FALSE,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_attendee (event_id, attendee_email),
    INDEX idx_tracking_code (tracking_code)
);

-- Optional: Insert sample QR code data for testing
-- INSERT INTO event_qr_codes (event_id, attendee_email, qr_code_url, tracking_code) VALUES
-- (1, 'user1@example.com', 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=TRACK:1:user1@example.com', 'TRACK:1:user1@example.com');

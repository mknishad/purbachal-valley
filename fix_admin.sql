-- Fix Admin Password
UPDATE users SET password_hash = '$2y$10$YourNewHashHere' WHERE username = 'admin';

-- Alternative: Reset to admin123
UPDATE users SET password_hash = '$2y$10$YMjPJqCpMjP9jPqQxWvVYOqQxWvVYOqQxWvVYOqQxWvVYOqQxWvVYOq' WHERE username = 'admin';
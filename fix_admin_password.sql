INSERT INTO users (username, email, password_hash, role, full_name, status)
VALUES ('admin', 'admin@purbachalvalley.com', '$2y$10$2JD2r38a03AUbhRWQaNEB.9czCr3oQMqC0o.GbPay5CMPwajngHle', 'admin', 'System Administrator', 'active')
ON DUPLICATE KEY UPDATE
    username = VALUES(username),
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    role = 'admin',
    full_name = VALUES(full_name),
    status = 'active';

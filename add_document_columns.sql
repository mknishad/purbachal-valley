-- Add extra document columns to members table
ALTER TABLE members ADD COLUMN photo VARCHAR(255) AFTER nominee_phone;
ALTER TABLE members ADD COLUMN signature VARCHAR(255) AFTER photo;
ALTER TABLE members ADD COLUMN nominee_nid_image VARCHAR(255) AFTER signature;
-- MR PURBACHAL VALLEY - Database Schema
-- Land Investment and Member Contribution Tracking System

-- 1. Users / Admin Accounts
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'accountant', 'member') DEFAULT 'member',
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- 2. Projects (Land/Property Projects)
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_name VARCHAR(200) NOT NULL,
    project_code VARCHAR(50) UNIQUE,
    location VARCHAR(255),
    description TEXT,
    total_land_area DECIMAL(12,2),
    land_unit VARCHAR(20) DEFAULT 'sqft',
    total_acquisition_cost DECIMAL(15,2) DEFAULT 0,
    acquisition_date DATE,
    project_type ENUM('residential', 'commercial', 'mixed', 'apartment') DEFAULT 'residential',
    status ENUM('planning', 'acquisition', 'development', '分配済み', 'completed') DEFAULT 'planning',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 3. Project Phases (Development phases)
CREATE TABLE IF NOT EXISTS project_phases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    phase_name VARCHAR(100) NOT NULL,
    phase_order INT,
    start_date DATE,
    end_date DATE,
    budget DECIMAL(15,2),
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- 4. Plots/Units within Projects
CREATE TABLE IF NOT EXISTS plots (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    plot_number VARCHAR(50),
    plot_size DECIMAL(10,2),
    size_unit VARCHAR(20) DEFAULT 'sqft',
    plot_type ENUM('residential', 'commercial', 'apartment', 'parking', 'common') DEFAULT 'residential',
    price_per_unit DECIMAL(12,2),
    total_price DECIMAL(15,2),
    status ENUM('available', 'reserved', 'sold', 'allocated', 'cancelled') DEFAULT 'available',
    allocation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- 5. Members
CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    membership_number VARCHAR(50) UNIQUE,
    user_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50),
    father_name VARCHAR(100),
    mother_name VARCHAR(100),
    gender ENUM('male', 'female', 'other'),
    date_of_birth DATE,
    nationality VARCHAR(50) DEFAULT 'Bangladeshi',
    nid_number VARCHAR(50),
    nid_image_front VARCHAR(255),
    nid_image_back VARCHAR(255),
    passport_number VARCHAR(50),
    birth_certificate VARCHAR(50),
    email VARCHAR(100),
    phone VARCHAR(20),
    alternative_phone VARCHAR(20),
    present_address TEXT,
    permanent_address TEXT,
    occupation VARCHAR(100),
    employer_name VARCHAR(150),
    annual_income DECIMAL(15,2),
    investment_type ENUM('individual', 'group', 'organization') DEFAULT 'individual',
    group_name VARCHAR(100),
    group_leader_id INT,
    nominee_name VARCHAR(100),
    nominee_relation VARCHAR(50),
    nominee_nid VARCHAR(50),
    nominee_phone VARCHAR(20),
    photo VARCHAR(255),
    kyc_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verification_date DATE,
    member_status ENUM('active', 'inactive', 'expelled') DEFAULT 'active',
    registration_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (group_leader_id) REFERENCES members(id) ON DELETE SET NULL
);

-- 6. Member Investment Plans
CREATE TABLE IF NOT EXISTS investment_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    project_id INT NOT NULL,
    plan_name VARCHAR(100),
    total_investment_amount DECIMAL(15,2) NOT NULL,
    installment_type ENUM('monthly', 'quarterly', 'yearly', 'one_time') DEFAULT 'monthly',
    installment_amount DECIMAL(12,2),
    total_installments INT,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'completed', 'cancelled', 'defaulted') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- 7. Payment Transactions / Contributions
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_number VARCHAR(50) UNIQUE,
    member_id INT NOT NULL,
    investment_plan_id INT,
    project_id INT,
    payment_type ENUM('installment', 'full_payment', 'additional', 'refund', 'adjustment') DEFAULT 'installment',
    payment_method ENUM('cash', 'bank_transfer', 'cheque', 'mobile_banking', 'card', 'other') DEFAULT 'cash',
    amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2),
    due_amount DECIMAL(12,2),
    bank_name VARCHAR(100),
    branch_name VARCHAR(100),
    cheque_number VARCHAR(50),
    transaction_id VARCHAR(100),
    payment_date DATE NOT NULL,
    due_date DATE,
    installment_number INT,
    reference_number VARCHAR(100),
    notes TEXT,
    receipt_generated ENUM('yes', 'no') DEFAULT 'no',
    received_by INT,
    approved_by INT,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approval_date TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (investment_plan_id) REFERENCES investment_plans(id) ON DELETE SET NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 8. Payment Receipts
CREATE TABLE IF NOT EXISTS receipts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    receipt_number VARCHAR(50) UNIQUE,
    payment_id INT NOT NULL,
    member_id INT NOT NULL,
    project_id INT,
    amount DECIMAL(12,2) NOT NULL,
    payment_date DATE,
    print_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    printed_by INT,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (printed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 9. Member Allocations (Plot/Unit Allocation)
CREATE TABLE IF NOT EXISTS allocations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    plot_id INT NOT NULL,
    project_id INT,
    allocation_type ENUM('full', 'partial', 'shared') DEFAULT 'full',
    share_percentage DECIMAL(5,2),
    allocation_date DATE,
    allocation_letter_date DATE,
    allocation_letter_number VARCHAR(50),
    transfer_date DATE,
    deed_number VARCHAR(50),
    registration_status ENUM('pending', 'registered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 10. Documents
CREATE TABLE IF NOT EXISTS documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    document_type ENUM('member_id', 'land_deed', 'agreement', 'noc', 'payment_receipt', 'legal', 'other') NOT NULL,
    member_id INT,
    project_id INT,
    plot_id INT,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    title VARCHAR(200),
    description TEXT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 11. Expenses
CREATE TABLE IF NOT EXISTS expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expense_number VARCHAR(50) UNIQUE,
    project_id INT,
    expense_category ENUM('land_purchase', 'development', 'legal', 'registration', 'tax', 'office', 'salary', 'marketing', 'other') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'cheque') DEFAULT 'cash',
    payment_date DATE NOT NULL,
    payee_name VARCHAR(150),
    description TEXT,
    bill_number VARCHAR(100),
    bill_date DATE,
    approved_by INT,
    status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 12. Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    member_id INT,
    notification_type ENUM('payment_reminder', 'due_alert', 'project_update', 'system', 'allocation', 'document') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    link VARCHAR(255),
    is_read ENUM('yes', 'no') DEFAULT 'no',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    sent_via ENUM('email', 'sms', 'app', 'all') DEFAULT 'app',
    send_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- 13. Audit Logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 14. Settings
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 15. SMS/Email Log
CREATE TABLE IF NOT EXISTS communications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    phone VARCHAR(20),
    email VARCHAR(100),
    communication_type ENUM('sms', 'email', 'whatsapp') DEFAULT 'sms',
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'delivered') DEFAULT 'pending',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO users (username, email, password_hash, role, full_name, status) 
VALUES ('admin', 'admin@purbachalvalley.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 'active');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('organization_name', 'MR PURBACHAL VALLEY', 'Organization/Project Name'),
('organization_address', 'Dhaka, Bangladesh', 'Organization Address'),
('currency', 'BDT', 'Currency Code'),
('currency_symbol', 'Tk', 'Currency Symbol'),
('date_format', 'Y-m-d', 'Date Format'),
('invoice_prefix', 'INV', 'Invoice/PReceipt Prefix'),
('payment_prefix', 'PMT', 'Payment Prefix'),
('membership_prefix', 'MRPV', 'Membership Number Prefix'),
('auto_approval', 'yes', 'Auto-approve payments'),
('email_notification', 'yes', 'Enable email notifications'),
('sms_notification', 'yes', 'Enable SMS notifications');
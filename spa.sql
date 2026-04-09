CREATE DATABASE IF NOT EXISTS spa_reservation;
USE spa_reservation;

-- ===================== USERS =====================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    full_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at DATETIME DEFAULT NOW()
);

-- ===================== SERVICES =====================
CREATE TABLE services (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100),
    category VARCHAR(50),
    duration INT COMMENT 'in minutes',
    price INT,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1
);

-- ===================== THERAPISTS =====================
CREATE TABLE therapists (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100),
    specialty VARCHAR(100),
    experience VARCHAR(50),
    phone VARCHAR(20),
    status ENUM('active','inactive') DEFAULT 'active'
);

-- ===================== BOOKINGS =====================
CREATE TABLE bookings (
    id VARCHAR(10) PRIMARY KEY,
    user_id INT,
    customer VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    service_id VARCHAR(10),
    therapist_id VARCHAR(10),
    date DATE,
    time TIME,
    end_time TIME COMMENT 'calculated from service duration',
    status ENUM('awaiting_payment','pending','confirmed','inprogress','done','cancelled') DEFAULT 'awaiting_payment',
    notes TEXT,
    created_at DATETIME DEFAULT NOW(),
    payment_deadline DATETIME COMMENT '1 hour after booking creation',
    FOREIGN KEY (service_id) REFERENCES services(id),
    FOREIGN KEY (therapist_id) REFERENCES therapists(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ===================== PAYMENTS =====================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(10) NOT NULL,
    proof_filename VARCHAR(255),
    proof_path VARCHAR(500),
    uploaded_at DATETIME,
    verified_at DATETIME,
    verified_by INT COMMENT 'admin user id',
    status ENUM('awaiting','uploaded','verified','rejected') DEFAULT 'awaiting',
    amount INT,
    notes TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- ===================== SEED DATA =====================
INSERT INTO users (username, password, role, full_name, phone, email) VALUES
('admin', MD5('admin123'), 'admin', 'Administrator', '081200000000', 'admin@serenityspa.com'),
('maya', MD5('maya123'), 'customer', 'Maya Sari', '081234567890', 'maya@mail.com'),
('john', MD5('john123'), 'customer', 'John Doe', '081234567891', 'john@mail.com'),

INSERT INTO services VALUES
('S001','Swedish Massage','massage',60,350000,'Gentle full-body massage to ease tension and improve circulation.',1),
('S002','Deep Tissue Massage','massage',90,480000,'Targets deep muscle layers to relieve chronic pain and stiffness.',1),
('S003','Hot Stone Therapy','massage',90,550000,'Heated basalt stones melt away muscle tension with lasting warmth.',1),
('S004','Brightening Facial','facial',60,420000,'Vitamin C infused treatment for luminous, even-toned skin.',1),
('S005','Anti-Aging Facial','facial',75,580000,'Collagen-boosting facial with microcurrent lifting technology.',1),
('S006','Balinese Body Scrub','body',45,280000,'Exfoliating rice and coconut scrub that renews skin radiance.',1),
('S007','Javanese Lulur','body',90,650000,'Traditional royal body treatment with turmeric lulur paste.',1),
('S008','Serenity Signature Package','package',180,1200000,'Full experience: scrub + massage + facial + refreshments.',1),
('S009','Couple Retreat','package',120,1800000,'Romantic side-by-side massage and facial for two in our VIP suite.',1);

INSERT INTO therapists VALUES
('T001','Dewi Rahayu','Massage Therapy','8 years','081300000001','active'),
('T002','Sari Pertiwi','Facial & Skincare','5 years','081300000002','active'),
('T003','Budi Santoso','Deep Tissue & Sports','10 years','081300000003','active'),
('T004','Anita Kusuma','Traditional Body Treatments','7 years','081300000004','active'),
('T005','Rizki Putra','Hot Stone & Aromatherapy','4 years','081300000005','active');

INSERT INTO bookings (id,user_id,customer,phone,email,service_id,therapist_id,date,time,end_time,status,notes,created_at,payment_deadline) VALUES
('BK001',2,'Maya Sari','081234567890','maya@mail.com','S001','T001','2024-12-20','10:00:00','11:00:00','done','',NOW(),NOW()),
('BK002',3,'John Doe','081234567891','john@mail.com','S002','T002','2024-12-21','14:00:00','15:30:00','confirmed','Need extra pillow',NOW(),NOW());

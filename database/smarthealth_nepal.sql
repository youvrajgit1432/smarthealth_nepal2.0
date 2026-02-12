-- SmartHealth Nepal Database Schema
-- Database: smarthealth
-- Created for sustainable healthcare queue management and chronic care tracking

CREATE DATABASE IF NOT EXISTS smarthealth;
USE smarthealth;

-- ============================================
-- 1. USERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    email VARCHAR(100),
    mpin VARCHAR(10),
    is_pregnant BOOLEAN DEFAULT FALSE,
    chronic_diseases JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- 2. DEPARTMENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_en VARCHAR(100) NOT NULL,
    name_ne VARCHAR(100) NOT NULL,
    description_en TEXT,
    description_ne TEXT,
    max_capacity INT DEFAULT 50,
    avg_service_time INT DEFAULT 30,
    current_load INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 3. TOKENS TABLE (Queue Management)
-- ============================================
CREATE TABLE IF NOT EXISTS tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    token_number INT NOT NULL,
    priority ENUM('Emergency', 'Priority', 'Normal', 'Chronic') DEFAULT 'Normal',
    triage_reason JSON,
    status ENUM('Active', 'Called', 'Completed', 'Missed', 'Rescheduled') DEFAULT 'Active',
    estimated_wait_time INT,
    is_emergency BOOLEAN DEFAULT FALSE,
    is_chronic_followup BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    called_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_department (department_id)
);

-- ============================================
-- 4. CHRONIC DISEASES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS chronic_diseases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    disease_name VARCHAR(100) NOT NULL,
    disease_code VARCHAR(20),
    diagnosis_date DATE,
    next_followup_date DATE,
    last_visit_date DATE NULL,
    medications JSON,
    doctor_notes TEXT,
    status ENUM('Active', 'Resolved', 'Suspended') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_followup (next_followup_date)
);

-- ============================================
-- 5. MATERNAL HEALTH TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS maternal_health (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    pregnancy_status ENUM('Pregnant', 'Post-Partum', 'Not Pregnant') DEFAULT 'Pregnant',
    expected_due_date DATE,
    last_menstrual_period DATE,
    antenatal_visits_completed INT DEFAULT 0,
    next_antenatal_date DATE,
    vaccinations_needed JSON,
    last_checkup_date DATE NULL,
    notes TEXT,
    is_high_risk BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_due_date (expected_due_date)
);

-- ============================================
-- 6. HEALTH RECORDS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS health_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_id INT,
    visit_date DATE,
    symptoms JSON,
    diagnosis TEXT,
    treatment_plan TEXT,
    doctor_name VARCHAR(100),
    department_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_visit_date (visit_date)
);

-- ============================================
-- 7. NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('Token', 'Chronic', 'Maternal', 'Appointment', 'System') DEFAULT 'System',
    message_en TEXT NOT NULL,
    message_ne TEXT NOT NULL,
    is_sms BOOLEAN DEFAULT FALSE,
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    delivery_status ENUM('Pending', 'Sent', 'Failed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (delivery_status)
);

-- ============================================
-- 8. ADMINS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(100),
    role ENUM('SuperAdmin', 'Admin', 'Officer', 'Staff') DEFAULT 'Staff',
    department_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- ============================================
-- 9. TRIAGE RESPONSES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS triage_responses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_id INT,
    has_fever BOOLEAN DEFAULT FALSE,
    fever_duration INT,
    difficulty_breathing BOOLEAN DEFAULT FALSE,
    injury BOOLEAN DEFAULT FALSE,
    injury_severity VARCHAR(50),
    pregnancy BOOLEAN DEFAULT FALSE,
    chronic_disease BOOLEAN DEFAULT FALSE,
    chronic_disease_names JSON,
    emergency_signs BOOLEAN DEFAULT FALSE,
    additional_notes TEXT,
    assigned_priority ENUM('Emergency', 'Priority', 'Normal', 'Chronic') DEFAULT 'Normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE SET NULL
);

-- ============================================
-- 10. SERVICES TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_en VARCHAR(100) NOT NULL,
    name_ne VARCHAR(100) NOT NULL,
    type ENUM('Emergency', 'Referral', 'Education', 'Regular') DEFAULT 'Regular',
    description_en TEXT,
    description_ne TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 11. REFERRALS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS referrals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    from_department_id INT,
    to_department_id INT,
    reason TEXT,
    referred_date DATE,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_date DATE NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (to_department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- ============================================
-- 12. OFFLINE BOOKINGS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS offline_bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) NOT NULL,
    patient_name VARCHAR(100),
    booked_by_staff_id INT,
    department_id INT NOT NULL,
    triage_classification ENUM('Emergency', 'Priority', 'Normal', 'Chronic') DEFAULT 'Normal',
    booking_mode ENUM('Assisted', 'SMS') DEFAULT 'Assisted',
    token_id INT,
    status ENUM('Pending', 'Converted', 'Expired') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (booked_by_staff_id) REFERENCES admins(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (token_id) REFERENCES tokens(id) ON DELETE SET NULL
);

-- ============================================
-- INITIAL DATA INSERTION
-- ============================================

-- Insert Departments
INSERT INTO departments (name_en, name_ne, description_en, description_ne, max_capacity, avg_service_time) VALUES
('General Medicine', 'सामान्य चिकित्सा', 'General health consultations and diagnoses', 'सामान्य स्वास्थ्य परामर्श', 50, 30),
('Emergency', 'आपातकाल', 'Emergency and critical care', 'आपातकालीन देखभाल', 20, 15),
('Maternal Health', 'मातृत्व स्वास्थ्य', 'Pregnancy and maternal care', 'गर्भावस्था र मातृत्व देखभाल', 30, 25),
('Chronic Disease', 'पुरानो रोग', 'Diabetes, hypertension, respiratory disease', 'मधुमेह, उच्च रक्तचाप', 40, 20),
('Pediatrics', 'शिशु रोग', 'Child health and vaccinations', 'बालरोग र टीकाकरण', 35, 25),
('Orthopedics', 'अस्थिरोग', 'Bone and joint disorders', 'हड्डी र जोड़ के विकार', 25, 30),
('Cardiology', 'हृदय रोग', 'Heart and cardiovascular diseases', 'ह्रदय रोग', 20, 35),
('ENT', 'नाक, कान, गला', 'Ear, Nose, and Throat', 'कान नाक गला', 30, 20);

-- Insert Sample Services
INSERT INTO services (name_en, name_ne, type, description_en, description_ne) VALUES
('Emergency Triage', 'आपातकाल जाँच', 'Emergency', 'Immediate assessment for emergencies', 'तत्काल आपातकालीन मूल्यांकन'),
('Chronic Disease Follow-up', 'दीर्घस्थायी रोग अनुवर्ती', 'Regular', 'Regular check-ups for chronic diseases', 'पुरानी बीमारियों की जांच'),
('Maternal Check-up', 'मातृत्व जाँच', 'Regular', 'Pregnancy and postnatal care', 'गर्भावस्था और प्रसवोत्तर देखभाल'),
('Vaccination', 'टीकाकरण', 'Regular', 'Immunization services', 'प्रतिरक्षा सेवाएं'),
('Referral Service', 'रेफरल सेवा', 'Referral', 'Referral to specialized departments', 'विशेष विभागों में रेफरल'),
('Health Education', 'स्वास्थ्य शिक्षा', 'Education', 'Health awareness and prevention tips', 'स्वास्थ्य जागरूकता और रोकथाम');

-- Insert Sample Admin Users
INSERT INTO admins (username, password_hash, full_name, email, role, is_active, created_at) VALUES
('admin', '$2y$10$aF9ZpM0Qv8vHkNV5a2ZaDeqPyF3L.ZKJ.QmVp1R0E5c5eZ2JZ6bHm', 'Super Admin', 'admin@smarthealth.local', 'SuperAdmin', TRUE, NOW());

-- Create indexes for better performance
CREATE INDEX idx_token_date ON tokens(created_at);
CREATE INDEX idx_chronic_date ON chronic_diseases(next_followup_date);
CREATE INDEX idx_maternal_date ON maternal_health(next_antenatal_date);
CREATE INDEX idx_notification_date ON notifications(created_at);

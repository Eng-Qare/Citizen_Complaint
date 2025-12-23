/* CITY COMPLAINT MANAGEMENT SYSTEM (BOSASO, SOMALIA)
    Complete Schema + Sample Data (Bosaso Context)
*/

-- -----------------------------------------------------
-- 1. DROP TABLES (IF THEY EXIST)
-- -----------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS system_logs, area_issue_trends, department_performance, daily_city_metrics, 
                     feedback, complaint_attachments, sla_instances, complaint_events, 
                     complaint_assignments, complaints, sentiment_levels, complaint_statuses, 
                     priorities, services, departments, areas, citizens, users, roles;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- 2. TABLE STRUCTURES
-- -----------------------------------------------------

CREATE TABLE roles (
    role_id TINYINT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE users (
    user_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_id TINYINT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE citizens (
    citizen_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL UNIQUE,
    national_id VARCHAR(50),
    address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE areas (
    area_id INT PRIMARY KEY AUTO_INCREMENT,
    district_name VARCHAR(100) NOT NULL,
    neighborhood_name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7)
);

CREATE TABLE departments (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL,
    head_user_id BIGINT NULL,
    is_active TINYINT DEFAULT 1,
    FOREIGN KEY (head_user_id) REFERENCES users(user_id)
);

CREATE TABLE services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    expected_resolution_hours INT,
    is_active TINYINT DEFAULT 1,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

CREATE TABLE priorities (
    priority_id TINYINT PRIMARY KEY,
    priority_name VARCHAR(50) NOT NULL UNIQUE,
    priority_level INT NOT NULL,
    default_response_hours INT NOT NULL,
    default_resolution_hours INT NOT NULL,
    color_code VARCHAR(10)
);

CREATE TABLE complaint_statuses (
    status_id TINYINT PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL UNIQUE,
    status_order INT NOT NULL,
    is_final TINYINT DEFAULT 0
);

CREATE TABLE sentiment_levels (
    sentiment_id TINYINT PRIMARY KEY,
    sentiment_label VARCHAR(50) NOT NULL,
    score INT NOT NULL,
    description TEXT
);

CREATE TABLE complaints (
    complaint_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    citizen_id BIGINT NOT NULL,
    service_id INT NOT NULL,
    area_id INT NOT NULL,
    priority_id TINYINT NOT NULL,
    current_status_id TINYINT NOT NULL,
    sentiment_id TINYINT NULL,
    description TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    FOREIGN KEY (citizen_id) REFERENCES citizens(citizen_id),
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    FOREIGN KEY (area_id) REFERENCES areas(area_id),
    FOREIGN KEY (priority_id) REFERENCES priorities(priority_id),
    FOREIGN KEY (current_status_id) REFERENCES complaint_statuses(status_id),
    FOREIGN KEY (sentiment_id) REFERENCES sentiment_levels(sentiment_id)
);

CREATE TABLE complaint_assignments (
    assignment_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    complaint_id BIGINT NOT NULL,
    assigned_department_id INT NOT NULL,
    assigned_user_id BIGINT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id),
    FOREIGN KEY (assigned_department_id) REFERENCES departments(department_id),
    FOREIGN KEY (assigned_user_id) REFERENCES users(user_id)
);

CREATE TABLE sla_instances (
    complaint_id BIGINT PRIMARY KEY,
    response_deadline DATETIME NOT NULL,
    resolution_deadline DATETIME NOT NULL,
    response_met TINYINT DEFAULT 0,
    resolution_met TINYINT DEFAULT 0,
    escalated TINYINT DEFAULT 0,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id)
);

CREATE TABLE feedback (
    feedback_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    complaint_id BIGINT NOT NULL UNIQUE,
    rating TINYINT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id)
);

-- -----------------------------------------------------
-- 3. DATA INSERTS (SOMALI NAMES & PLACES)
-- -----------------------------------------------------

-- Roles
INSERT INTO roles (role_name) VALUES ('Admin'), ('Dept Head'), ('Officer'), ('Citizen');

-- Users
INSERT INTO users (role_id, full_name, email, phone, password_hash) VALUES 
(1, 'Ahmed Mohamed Abdi', 'ahmed@gov.so', '+252615111111', 'hash123'),
(2, 'Fartun Hassan Ali', 'fartun@gov.so', '+252615222222', 'hash123'),
(3, 'Abdirahman Muse Said', 'abdi@gov.so', '+252615333333', 'hash123'),
(4, 'Sahra Yusuf Warsame', 'sahra@email.com', '+252615444444', 'hash123');

-- Citizen Profile
INSERT INTO citizens (user_id, national_id, address) VALUES 
(4, 'NID-SOM-100200', 'Guryosamo, Bosaso, Puntland');

-- Areas (Bosaso Neighborhoods)
INSERT INTO areas (district_name, neighborhood_name) VALUES 
('Bosaso', 'New Bosaso'),
('Bosaso', 'Guryosamo'),
('Bosaso', 'Biyo Kulule'),
('Bosaso', 'Raas Caseyr');

-- Departments & Services
INSERT INTO departments (department_name, head_user_id) VALUES 
('Nadaafadda (Sanitation)', 2),
('Biyaha (Water Dept)', 3);

INSERT INTO services (department_id, service_name, expected_resolution_hours) VALUES 
(1, 'Uruurinta Qashinka (Trash)', 24),
(1, 'Bullaacada xiran (Sewage)', 12),
(2, 'Dhuumaha dillaacay (Leaks)', 8);

-- Statuses & Priorities
INSERT INTO priorities (priority_id, priority_name, priority_level, default_response_hours, default_resolution_hours, color_code) VALUES 
(1, 'Degdeg (Urgent)', 3, 2, 8, '#FF0000'),
(2, 'Caadi (Normal)', 1, 12, 48, '#00FF00');

INSERT INTO complaint_statuses (status_id, status_name, status_order, is_final) VALUES 
(1, 'La gudbiyey (Submitted)', 1, 0),
(2, 'Gacanta lagu hayaa (In Progress)', 2, 0),
(3, 'Waa la xalliyey (Resolved)', 3, 1);

INSERT INTO sentiment_levels (sentiment_id, sentiment_label, score) VALUES 
(1, 'Xanaaqsan (Angry)', 1),
(2, 'Dhexdhexaad (Neutral)', 3),
(3, 'Faraxsan (Happy)', 5);

-- Sample Complaint
INSERT INTO complaints (citizen_id, service_id, area_id, priority_id, current_status_id, sentiment_id, description) VALUES 
(1, 2, 2, 1, 1, 1, 'Bullaacada ayaa dillaacday agagaarka isgoyska New Bosaso.');
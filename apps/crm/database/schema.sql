-- Users & Roles
CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'employee',
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  department VARCHAR(50),
  manager_id INT DEFAULT NULL,
  mfa_secret VARCHAR(64),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- Attendance
CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  clock_in DATETIME NOT NULL,
  clock_out DATETIME DEFAULT NULL,
  notes VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES users(id)
);

-- Leaves
CREATE TABLE IF NOT EXISTS leaves (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason VARCHAR(255),
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES users(id)
);

-- Learning (LMS lite)
CREATE TABLE IF NOT EXISTS learning_courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  category VARCHAR(100),
  level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS learning_enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  user_id INT NOT NULL,
  progress DECIMAL(5,2) DEFAULT 0,
  status ENUM('enrolled','completed','dropped') DEFAULT 'enrolled',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES learning_courses(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- CRM Entities (initial)
CREATE TABLE IF NOT EXISTS leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120),
  phone VARCHAR(20),
  source VARCHAR(80),
  owner_id INT DEFAULT NULL,
  status ENUM('unassigned','open','won','lost') DEFAULT 'unassigned',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT DEFAULT NULL,
  assigned_to INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  priority ENUM('low','medium','high') DEFAULT 'medium',
  status ENUM('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  deadline DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (assigned_to) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS task_comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  task_id INT NOT NULL,
  user_id INT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (task_id) REFERENCES tasks(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
-- === PHASE 2: HR (Employees) ===

-- 1) Departments
CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  code VARCHAR(20) UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2) Designations
CREATE TABLE IF NOT EXISTS designations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  level INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3) Extend users with HR fields
ALTER TABLE users
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN department_id INT NULL,
  ADD COLUMN designation_id INT NULL,
  ADD COLUMN doj DATE NULL,
  ADD COLUMN employee_code VARCHAR(30) UNIQUE NULL,
  ADD CONSTRAINT fk_users_department
    FOREIGN KEY (department_id) REFERENCES departments(id),
  ADD CONSTRAINT fk_users_designation
    FOREIGN KEY (designation_id) REFERENCES designations(id);

-- 4) Employee profiles (extended details)
CREATE TABLE IF NOT EXISTS employee_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  phone VARCHAR(20),
  alt_phone VARCHAR(20),
  dob DATE NULL,
  gender ENUM('male','female','other') NULL,
  blood_group VARCHAR(5) NULL,
  address_line1 VARCHAR(150),
  address_line2 VARCHAR(150),
  city VARCHAR(80),
  state VARCHAR(80),
  postal_code VARCHAR(15),
  country VARCHAR(80),
  emergency_contact_name VARCHAR(120),
  emergency_contact_phone VARCHAR(20),
  PAN VARCHAR(20),
  AADHAAR VARCHAR(20),
  bank_name VARCHAR(100),
  bank_account VARCHAR(40),
  ifsc VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 5) Employee documents (KYC / HR docs)
CREATE TABLE IF NOT EXISTS employee_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  doc_type VARCHAR(50) NOT NULL,   -- e.g., PAN, AADHAAR, OFFER_LETTER
  file_name VARCHAR(200) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  mime_type VARCHAR(100),
  size BIGINT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 6) Employee assets (laptops, etc.)
CREATE TABLE IF NOT EXISTS employee_assets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_name VARCHAR(120) NOT NULL,
  serial_no VARCHAR(120),
  status ENUM('assigned','returned','lost','damaged') DEFAULT 'assigned',
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  returned_at DATETIME NULL,
  notes TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Seed a couple of departments/designations (idempotent)
INSERT INTO departments (name, code) VALUES
  ('Engineering','ENG'),
  ('Marketing','MKT')
ON DUPLICATE KEY UPDATE name=VALUES(name), code=VALUES(code);

INSERT INTO designations (name, level) VALUES
  ('Intern',1), ('Associate',2), ('Manager',3), ('Director',4)
ON DUPLICATE KEY UPDATE name=VALUES(name), level=VALUES(level);


-- === PHASE 3: Attendance & Shifts ===

-- 1) Shifts (company-wide templates)
CREATE TABLE IF NOT EXISTS shifts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  -- 0=Sunday ... 6=Saturday; comma-separated e.g. "1,2,3,4,5"
  working_days VARCHAR(50) NOT NULL DEFAULT '1,2,3,4,5',
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  grace_minutes INT NOT NULL DEFAULT 10,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2) Attendance policies (IP, geofence, device)
CREATE TABLE IF NOT EXISTS attendance_policies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  ip_allowlist TEXT NULL,                     -- CSV of CIDR or IPs
  geo_lat DECIMAL(10,7) NULL,
  geo_lng DECIMAL(10,7) NULL,
  geo_radius_m INT NULL,                      -- meters
  device_bind TINYINT(1) NOT NULL DEFAULT 0,  -- if 1, require registered device
  require_photo TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3) Shift assignments
CREATE TABLE IF NOT EXISTS shift_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  shift_id INT NOT NULL,
  policy_id INT NULL,
  effective_from DATE NOT NULL,
  effective_to DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (shift_id) REFERENCES shifts(id),
  FOREIGN KEY (policy_id) REFERENCES attendance_policies(id),
  INDEX (user_id, effective_from)
);

-- 4) Attendance exceptions (late, outside geo/ip, etc.)
CREATE TABLE IF NOT EXISTS attendance_exceptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATE NOT NULL,
  type ENUM('late','early_out','outside_geo','ip_denied','no_assignment','duplicate_out','duplicate_in','device_mismatch') NOT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  UNIQUE KEY uniq_user_date_type (user_id, date, type)
);

-- 5) Extend attendance with policy & meta
ALTER TABLE attendance
  ADD COLUMN shift_id INT NULL,
  ADD COLUMN policy_id INT NULL,
  ADD COLUMN ip_used VARCHAR(64) NULL,
  ADD COLUMN location_lat DECIMAL(10,7) NULL,
  ADD COLUMN location_lng DECIMAL(10,7) NULL,
  ADD COLUMN device_id VARCHAR(120) NULL,
  ADD COLUMN source VARCHAR(40) NULL,
  ADD COLUMN worked_minutes INT NULL,
  ADD FOREIGN KEY (shift_id) REFERENCES shifts(id),
  ADD FOREIGN KEY (policy_id) REFERENCES attendance_policies(id);

-- 6) Seed a default office policy & general shift (idempotent)
INSERT INTO attendance_policies (name, ip_allowlist, geo_lat, geo_lng, geo_radius_m, device_bind, require_photo)
VALUES ('Default Office', NULL, NULL, NULL, NULL, 0, 0)
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO shifts (name, working_days, start_time, end_time, grace_minutes)
VALUES ('General', '1,2,3,4,5', '10:00:00', '19:00:00', 10)
ON DUPLICATE KEY UPDATE name=VALUES(name);
-- === PHASE 4: Leave Management ===

-- 1) Leave Types (company-wide)
CREATE TABLE IF NOT EXISTS leave_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(30) NOT NULL UNIQUE,
  unit ENUM('day','hour') NOT NULL DEFAULT 'day',
  default_annual_quota DECIMAL(6,2) NOT NULL DEFAULT 0,
  carry_forward_max DECIMAL(6,2) NOT NULL DEFAULT 0,
  negative_balance_allowed TINYINT(1) NOT NULL DEFAULT 0,
  requires_document TINYINT(1) NOT NULL DEFAULT 0,
  gender_restriction ENUM('male','female','other') NULL,
  min_notice_days INT NOT NULL DEFAULT 0,
  max_consecutive_days DECIMAL(6,2) NULL,
  weekend_inclusive TINYINT(1) NOT NULL DEFAULT 0,
  half_day_allowed TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2) Holidays
CREATE TABLE IF NOT EXISTS holidays (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  date DATE NOT NULL,
  region VARCHAR(80) NULL,
  is_optional TINYINT(1) NOT NULL DEFAULT 0,
  UNIQUE KEY uniq_date_region (date, COALESCE(region, ''))
);

-- 3) Yearly Entitlements (quota assignment per user/type/year)
CREATE TABLE IF NOT EXISTS leave_entitlements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type_id INT NOT NULL,
  year SMALLINT NOT NULL,
  quota DECIMAL(6,2) NOT NULL DEFAULT 0,
  carried_forward DECIMAL(6,2) NOT NULL DEFAULT 0,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_type_year (user_id, type_id, year),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (type_id) REFERENCES leave_types(id)
);

-- 4) (Optional) Cached balances (can be recomputed on demand)
CREATE TABLE IF NOT EXISTS leave_balances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type_id INT NOT NULL,
  year SMALLINT NOT NULL,
  balance DECIMAL(7,2) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_type_year (user_id, type_id, year),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (type_id) REFERENCES leave_types(id)
);

-- 5) Leave Requests and Approvals
CREATE TABLE IF NOT EXISTS leave_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  half_day TINYINT(1) NOT NULL DEFAULT 0,
  days DECIMAL(6,2) NOT NULL DEFAULT 0,  -- computed effective days
  reason VARCHAR(255),
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  doc_file VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (type_id) REFERENCES leave_types(id),
  INDEX (user_id, start_date, end_date)
);

CREATE TABLE IF NOT EXISTS leave_approvals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  request_id INT NOT NULL,
  step_no INT NOT NULL,
  approver_id INT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  comment VARCHAR(255) NULL,
  acted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (request_id) REFERENCES leave_requests(id),
  FOREIGN KEY (approver_id) REFERENCES users(id),
  UNIQUE KEY uniq_request_step (request_id, step_no)
);

-- Helpful seed type
INSERT INTO leave_types (name, code, default_annual_quota, carry_forward_max, weekend_inclusive, half_day_allowed)
VALUES ('Annual Leave','AL', 18, 6, 0, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- === PHASE 5: LMS (Courses, Modules, Lessons, Quizzes, Enrollments, Certificates) ===

-- Courses
CREATE TABLE IF NOT EXISTS lms_courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  category VARCHAR(120),
  level ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
  language VARCHAR(40),
  duration_minutes INT DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Modules
CREATE TABLE IF NOT EXISTS lms_modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  position INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES lms_courses(id) ON DELETE CASCADE
);

-- Lessons
CREATE TABLE IF NOT EXISTS lms_lessons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  type ENUM('video','article','file','quiz') NOT NULL DEFAULT 'article',
  content TEXT NULL,                    -- markdown / html for articles
  video_url VARCHAR(500) NULL,
  file_path VARCHAR(255) NULL,
  quiz_id INT NULL,
  duration_minutes INT DEFAULT 0,
  position INT NOT NULL DEFAULT 1,
  is_preview TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (module_id) REFERENCES lms_modules(id) ON DELETE CASCADE
);

-- Quizzes
CREATE TABLE IF NOT EXISTS lms_quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  passing_score INT NOT NULL DEFAULT 60,     -- percent
  attempts_allowed INT NOT NULL DEFAULT 3,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES lms_courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lms_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  text TEXT NOT NULL,
  type ENUM('single','multiple','truefalse') NOT NULL DEFAULT 'single',
  points DECIMAL(6,2) NOT NULL DEFAULT 1,
  position INT NOT NULL DEFAULT 1,
  FOREIGN KEY (quiz_id) REFERENCES lms_quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lms_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  question_id INT NOT NULL,
  text TEXT NOT NULL,
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (question_id) REFERENCES lms_questions(id) ON DELETE CASCADE
);

-- Enrollments & progress
CREATE TABLE IF NOT EXISTS lms_enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  user_id INT NOT NULL,
  status ENUM('enrolled','completed','dropped') DEFAULT 'enrolled',
  progress DECIMAL(5,2) DEFAULT 0,
  last_lesson_id INT NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  completed_at TIMESTAMP NULL,
  UNIQUE KEY uniq_course_user (course_id, user_id),
  FOREIGN KEY (course_id) REFERENCES lms_courses(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS lms_lesson_progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  lesson_id INT NOT NULL,
  completed TINYINT(1) NOT NULL DEFAULT 0,
  completed_at TIMESTAMP NULL,
  UNIQUE KEY uniq_user_lesson (user_id, lesson_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (lesson_id) REFERENCES lms_lessons(id) ON DELETE CASCADE
);

-- Attempts
CREATE TABLE IF NOT EXISTS lms_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT NOT NULL,
  user_id INT NOT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  submitted_at TIMESTAMP NULL,
  score DECIMAL(6,2) NULL,                 -- percent
  status ENUM('in_progress','submitted','passed','failed') DEFAULT 'in_progress',
  FOREIGN KEY (quiz_id) REFERENCES lms_quizzes(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS lms_attempt_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT NOT NULL,
  question_id INT NOT NULL,
  selected_option_ids TEXT NULL,           -- CSV of option ids
  is_correct TINYINT(1) NOT NULL DEFAULT 0,
  earned_points DECIMAL(6,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (attempt_id) REFERENCES lms_attempts(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES lms_questions(id) ON DELETE CASCADE
);

-- Certificates
CREATE TABLE IF NOT EXISTS lms_certificates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  user_id INT NOT NULL,
  cert_code VARCHAR(40) NOT NULL UNIQUE,
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (course_id) REFERENCES lms_courses(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Helpful seeds
INSERT INTO lms_courses (title, description, is_published)
VALUES ('Onboarding 101', 'Welcome to the company! Basics and policies.', 1)
ON DUPLICATE KEY UPDATE title=VALUES(title);


-- === PHASE 6: CRM Core ===

-- 1) Accounts (companies)
CREATE TABLE IF NOT EXISTS accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  domain VARCHAR(120) UNIQUE NULL,
  phone VARCHAR(30) NULL,
  website VARCHAR(200) NULL,
  address_line1 VARCHAR(150), address_line2 VARCHAR(150),
  city VARCHAR(80), state VARCHAR(80), postal_code VARCHAR(20), country VARCHAR(80),
  owner_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- 2) Contacts (people)
CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  account_id INT NULL,
  first_name VARCHAR(80),
  last_name VARCHAR(80),
  email VARCHAR(150) UNIQUE,
  phone VARCHAR(30),
  title VARCHAR(120),
  owner_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- 3) Pipelines & Stages
CREATE TABLE IF NOT EXISTS pipelines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pipeline_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  position INT NOT NULL DEFAULT 1,
  is_won TINYINT(1) NOT NULL DEFAULT 0,
  is_lost TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (pipeline_id) REFERENCES pipelines(id) ON DELETE CASCADE
);

-- 4) Deals
CREATE TABLE IF NOT EXISTS deals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  account_id INT NULL,
  contact_id INT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  pipeline_id INT NOT NULL,
  stage_id INT NOT NULL,
  status ENUM('open','won','lost') NOT NULL DEFAULT 'open',
  owner_id INT NULL,
  close_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
  FOREIGN KEY (pipeline_id) REFERENCES pipelines(id),
  FOREIGN KEY (stage_id) REFERENCES stages(id),
  FOREIGN KEY (owner_id) REFERENCES users(id),
  INDEX (pipeline_id, stage_id)
);

-- 5) Activities (log of calls/emails/meetings/tasks)
CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('call','email','meeting','task') NOT NULL,
  subject VARCHAR(200) NOT NULL,
  description TEXT,
  due_date DATETIME NULL,
  status ENUM('open','done') NOT NULL DEFAULT 'open',
  owner_id INT NOT NULL,
  account_id INT NULL,
  contact_id INT NULL,
  deal_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id),
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
  FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
  INDEX (deal_id), INDEX (account_id), INDEX (contact_id)
);

-- 6) Products
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(60) UNIQUE,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 7) Quotes
CREATE TABLE IF NOT EXISTS quotes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quote_no VARCHAR(40) UNIQUE NOT NULL,
  account_id INT NOT NULL,
  contact_id INT NULL,
  deal_id INT NULL,
  owner_id INT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  status ENUM('draft','sent','accepted','declined') NOT NULL DEFAULT 'draft',
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES accounts(id),
  FOREIGN KEY (contact_id) REFERENCES contacts(id),
  FOREIGN KEY (deal_id) REFERENCES deals(id),
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS quote_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quote_id INT NOT NULL,
  product_id INT NULL,
  name VARCHAR(200) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  qty DECIMAL(10,2) NOT NULL DEFAULT 1,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0, -- percent
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- 8) Invoices
CREATE TABLE IF NOT EXISTS invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(40) UNIQUE NOT NULL,
  account_id INT NOT NULL,
  contact_id INT NULL,
  quote_id INT NULL,
  owner_id INT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'INR',
  status ENUM('draft','sent','paid','overdue') NOT NULL DEFAULT 'draft',
  due_date DATE NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (account_id) REFERENCES accounts(id),
  FOREIGN KEY (contact_id) REFERENCES contacts(id),
  FOREIGN KEY (quote_id) REFERENCES quotes(id),
  FOREIGN KEY (owner_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS invoice_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT NOT NULL,
  product_id INT NULL,
  name VARCHAR(200) NOT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  qty DECIMAL(10,2) NOT NULL DEFAULT 1,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_rate DECIMAL(5,2) NOT NULL DEFAULT 0, -- percent
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- 9) Doc sequences (for quote/invoice numbers)
CREATE TABLE IF NOT EXISTS doc_sequences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seq_type VARCHAR(20) NOT NULL, -- 'QUOTE' | 'INV'
  seq_year INT NOT NULL,
  next_seq INT NOT NULL DEFAULT 1,
  UNIQUE KEY uniq_type_year (seq_type, seq_year)
);

-- Helpful seeds (pipeline + stages)
INSERT INTO pipelines (name) VALUES ('Default')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO stages (pipeline_id, name, position, is_won, is_lost)
SELECT p.id, s.name, s.position, s.is_won, s.is_lost
FROM (SELECT 1 AS position, 'New' AS name, 0 AS is_won, 0 AS is_lost
      UNION ALL SELECT 2, 'Qualified', 0, 0
      UNION ALL SELECT 3, 'Proposal', 0, 0
      UNION ALL SELECT 4, 'Negotiation', 0, 0
      UNION ALL SELECT 5, 'Won', 1, 0
      UNION ALL SELECT 6, 'Lost', 0, 1) s
JOIN pipelines p ON p.name='Default'
WHERE NOT EXISTS (SELECT 1 FROM stages st WHERE st.pipeline_id = p.id);

-- === PHASE 6.1: Leads + Conversion ===
CREATE TABLE IF NOT EXISTS leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(80),
  last_name VARCHAR(80),
  email VARCHAR(150),
  phone VARCHAR(30),
  company VARCHAR(200),
  title VARCHAR(120),
  source VARCHAR(80),
  status ENUM('new','working','qualified','unqualified','converted') DEFAULT 'new',
  owner_id INT NULL,
  account_id INT NULL,
  contact_id INT NULL,
  deal_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id),
  FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE SET NULL,
  FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
  FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL,
  INDEX (email), INDEX (phone), INDEX (company), INDEX (status), INDEX (owner_id)
);

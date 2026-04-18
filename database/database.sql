CREATE DATABASE IF NOT EXISTS lead_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lead_management;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  role ENUM('Admin','Staff') NOT NULL DEFAULT 'Staff',
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at DATETIME NOT NULL,
  deleted_at DATETIME NULL
);

CREATE TABLE leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_type ENUM('Business','Student') NOT NULL DEFAULT 'Business',
  full_name VARCHAR(150) NOT NULL,
  company_name VARCHAR(150) NULL,
  category VARCHAR(120) NULL,
  city VARCHAR(100) NULL,
  country VARCHAR(100) NULL,
  phone VARCHAR(40) NULL,
  whatsapp VARCHAR(40) NULL,
  email VARCHAR(150) NULL,
  website VARCHAR(255) NULL,
  facebook_link VARCHAR(255) NULL,
  instagram_link VARCHAR(255) NULL,
  linkedin_link VARCHAR(255) NULL,
  google_maps_link VARCHAR(255) NULL,
  source VARCHAR(80) NOT NULL DEFAULT 'Other',
  service_interest VARCHAR(120) NOT NULL DEFAULT 'Other',
  budget_range VARCHAR(80) NULL,
  priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
  stage VARCHAR(80) NOT NULL DEFAULT 'New Lead',
  assigned_user_id INT NULL,
  preferred_contact_method VARCHAR(60) NULL,
  pain_point TEXT NULL,
  owner_remarks TEXT NULL,
  approval_flag TINYINT(1) NOT NULL DEFAULT 0,
  closed_reason VARCHAR(255) NULL,
  last_contact_date DATE NULL,
  next_follow_up_date DATE NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_leads_user FOREIGN KEY (assigned_user_id) REFERENCES users(id)
);

CREATE TABLE lead_stage_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  stage VARCHAR(80) NOT NULL,
  changed_by INT NOT NULL,
  changed_at DATETIME NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (changed_by) REFERENCES users(id)
);

CREATE TABLE lead_interactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  interaction_type VARCHAR(60) NOT NULL,
  note TEXT NULL,
  next_action VARCHAR(255) NULL,
  next_follow_up_date DATE NULL,
  interaction_at DATETIME NOT NULL,
  created_by INT NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE lead_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  note TEXT NOT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE lead_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NULL,
  title VARCHAR(150) NOT NULL,
  assigned_user_id INT NOT NULL,
  due_date DATE NOT NULL,
  priority ENUM('Low','Medium','High') NOT NULL DEFAULT 'Medium',
  status ENUM('Pending','In Progress','Done','Cancelled') NOT NULL DEFAULT 'Pending',
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (assigned_user_id) REFERENCES users(id)
);

CREATE TABLE meetings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  meeting_title VARCHAR(150) NOT NULL,
  meeting_type ENUM('call','online meeting','physical meeting','discussion') NOT NULL,
  meeting_date DATE NOT NULL,
  meeting_time TIME NOT NULL,
  meeting_notes TEXT NULL,
  outcome VARCHAR(255) NULL,
  next_action VARCHAR(255) NULL,
  assigned_user_id INT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (assigned_user_id) REFERENCES users(id)
);

CREATE TABLE proposals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  proposal_date DATE NOT NULL,
  amount DECIMAL(12,2) NULL,
  status ENUM('Draft','Sent','Under Review','Accepted','Rejected') NOT NULL DEFAULT 'Draft',
  description TEXT NULL,
  file_path VARCHAR(255) NULL,
  notes TEXT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE lead_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  category VARCHAR(60) NOT NULL DEFAULT 'Other',
  note VARCHAR(255) NULL,
  uploaded_by INT NOT NULL,
  uploaded_at DATETIME NOT NULL,
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

CREATE TABLE message_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  channel VARCHAR(60) NOT NULL,
  category VARCHAR(80) NULL,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  created_by INT NOT NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(80) NOT NULL UNIQUE,
  `value` TEXT NULL
);

CREATE TABLE tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE lead_tags (
  lead_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (lead_id, tag_id),
  FOREIGN KEY (lead_id) REFERENCES leads(id),
  FOREIGN KEY (tag_id) REFERENCES tags(id)
);

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) NULL,
  entity_id INT NULL,
  description VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  title VARCHAR(150) NOT NULL,
  body VARCHAR(255) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (full_name,email,phone,role,password_hash,status,created_at) VALUES
('System Admin','admin@example.com','+1-555-0100','Admin','$2y$10$bi7NGwEoVeIr9ytWfKBwQOHg4a7vXl1QbG3VrmYfWXa6xRATYRREm','Active',NOW()),
('Sales Staff','staff@example.com','+1-555-0101','Staff','$2y$10$Jf4cS7uJl5Yjj6inUeJ6LeW6W4FbZPKuRjVjvW2VQWfU7p.TSKD8i','Active',NOW());

INSERT INTO leads (lead_type,full_name,company_name,category,city,country,phone,whatsapp,email,source,service_interest,priority,stage,assigned_user_id,pain_point,approval_flag,next_follow_up_date,created_at,updated_at) VALUES
('Business','Aisha Khan','Blue Pixel Studio','Marketing','Dallas','USA','+1-555-2010','+1-555-2010','aisha@bluepixel.com','LinkedIn','Social Media Management','High','Interested',2,'Needs better engagement and consistent posting',0,CURDATE()+INTERVAL 2 DAY,NOW()-INTERVAL 6 DAY,NOW()),
('Business','Rohit Verma','Nova Foods','Food','Austin','USA','+1-555-2011','+1-555-2011','rohit@novafoods.com','Google Maps','Company Profile / Portfolio','Medium','Contacted',2,'Needs company profile website refresh',0,CURDATE()+INTERVAL 1 DAY,NOW()-INTERVAL 4 DAY,NOW()),
('Student','Mina Ali','-','Education','Chicago','USA','+1-555-2012','+1-555-2012','mina@studentmail.com','Instagram','Online Frontend Classes','Medium','Replied',2,'Wants live classes for frontend basics',0,CURDATE()+INTERVAL 3 DAY,NOW()-INTERVAL 3 DAY,NOW()),
('Student','Tariq Noor','-','Education','New York','USA','+1-555-2013','+1-555-2013','tariq@studentmail.com','WhatsApp','Student Project','Low','New Lead',2,'Project request pending approval',1,CURDATE()+INTERVAL 5 DAY,NOW()-INTERVAL 1 DAY,NOW());

INSERT INTO lead_stage_history (lead_id,stage,changed_by,changed_at) VALUES
(1,'Interested',2,NOW()-INTERVAL 1 DAY),
(2,'Contacted',2,NOW()-INTERVAL 1 DAY),
(3,'Replied',2,NOW()-INTERVAL 1 DAY),
(4,'New Lead',2,NOW()-INTERVAL 1 DAY);

INSERT INTO lead_interactions (lead_id,interaction_type,note,next_action,next_follow_up_date,interaction_at,created_by) VALUES
(1,'Call made','Discussed monthly social package','Send proposal',CURDATE()+INTERVAL 1 DAY,NOW()-INTERVAL 1 DAY,2),
(2,'Email sent','Shared profile website examples','Schedule meeting',CURDATE()+INTERVAL 2 DAY,NOW()-INTERVAL 2 DAY,2),
(3,'Replied','Student confirmed availability','Send class schedule',CURDATE()+INTERVAL 1 DAY,NOW()-INTERVAL 1 DAY,2);

INSERT INTO lead_notes (lead_id,note,created_by,created_at) VALUES
(1,'Interested in 3-month contract',2,NOW()-INTERVAL 1 DAY),
(3,'Prefers evening class timings',2,NOW()-INTERVAL 1 DAY);

INSERT INTO lead_tasks (lead_id,title,assigned_user_id,due_date,priority,status,note,created_at) VALUES
(1,'Send social media proposal',2,CURDATE()+INTERVAL 1 DAY,'High','Pending','Include campaign options',NOW()),
(3,'Share class curriculum',2,CURDATE(),'Medium','In Progress','Send PDF outline',NOW());

INSERT INTO meetings (lead_id,meeting_title,meeting_type,meeting_date,meeting_time,meeting_notes,outcome,next_action,assigned_user_id,created_at) VALUES
(1,'Marketing package discussion','online meeting',CURDATE()+INTERVAL 1 DAY,'14:00:00','Discuss plan tiers','Awaiting final budget','Send revised pricing',2,NOW()),
(2,'Website scope call','call',CURDATE()+INTERVAL 2 DAY,'11:30:00','Collect pages and goals','Scope shared','Prepare proposal',2,NOW());

INSERT INTO proposals (lead_id,title,proposal_date,amount,status,description,notes,created_by,created_at) VALUES
(1,'Social Media Retainer Plan',CURDATE()-INTERVAL 1 DAY,1200,'Sent','3-month management + content calendar','Client reviewing',2,NOW()),
(2,'Company Profile Website Package',CURDATE(),850,'Draft','5-page responsive website','Pending approval from lead owner',2,NOW());

INSERT INTO message_templates (channel,category,title,body,created_by,created_at) VALUES
('WhatsApp','First Contact','Intro for Business Lead','Hi {{name}}, we help businesses improve social media visibility and lead flow. Would you like a quick 10-minute call?',1,NOW()),
('Email','Follow-up','Proposal Follow-up','Hello {{name}}, just checking if you had time to review the proposal. Happy to adjust scope based on your priorities.',1,NOW()),
('LinkedIn','Outreach','LinkedIn Intro','Hi {{name}}, noticed your brand online and thought we could help with frontend site + campaign support.',1,NOW());

INSERT INTO settings (`key`,`value`) VALUES
('company_name','Startup Team'),('system_title','Lead Management Software'),('default_pagination','15'),('upload_size_mb','5'),('timezone','UTC'),('currency_symbol','$'),('report_branding','Professional Lead Report'),('company_logo',''),('company_favicon','');

INSERT INTO activity_logs (user_id,action,entity_type,entity_id,description,created_at) VALUES
(1,'seed_data_loaded','system',NULL,'Initial sample data inserted',NOW()-INTERVAL 1 DAY),
(2,'lead_created','lead',1,'Lead imported from LinkedIn',NOW()-INTERVAL 8 HOUR),
(2,'proposal_added','proposal',1,'Proposal sent to Blue Pixel Studio',NOW()-INTERVAL 2 HOUR);

INSERT INTO notifications (user_id,title,body,is_read,created_at) VALUES
(NULL,'Welcome to LeadManager CRM','Your CRM is ready. Review dashboard and start assigning leads.',0,NOW()-INTERVAL 2 HOUR),
(2,'Follow-up Reminder','You have leads due for follow-up today.',0,NOW()-INTERVAL 1 HOUR);

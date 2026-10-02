-- =====================================================================
-- SmartHealth Nepal 2.0 - Canonical Database Schema & Demo Seed
-- =====================================================================
-- This is the single authoritative schema for the project. Import it once
-- into a fresh MySQL/MariaDB instance to create the `smarthealth` database
-- with all tables, the `nearby_hospitals_view`, and reproducible fictional
-- demo data (hospitals, departments, staff, admin accounts, patients and
-- tokens).
--
-- It was assembled from the original project dump recovered from git
-- history plus a systematic analysis of the application source code
-- (models, controllers, APIs, admin panel and hospital portal).
-- Older partial recovery dumps are kept in database/archive/ for provenance only.
--
-- Everything is utf8mb4 / utf8mb4_unicode_ci so Nepali (Devanagari)
-- text renders correctly.
--
-- Database name: `smarthealth`
--   This is the name the executable code expects in
--   backend/config/database.php -> DB_NAME.
--
-- NOTE: every row below is FICTIONAL demo/seed data.
--   The demo login credentials are LOCAL DEVELOPMENT ONLY. Delete or
--   replace them before any production deployment.
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `smarthealth`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smarthealth`;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 1. CORE LOOKUP TABLES
-- =====================================================================

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `name_en` varchar(100) DEFAULT NULL,
  `name_ne` varchar(100) DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_ne` text DEFAULT NULL,
  `max_capacity` int(11) DEFAULT 50,
  `avg_service_time` int(11) DEFAULT 30,
  `current_load` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  -- legacy/compat columns used by the older admin controllers
  `hospital_id` int(11) DEFAULT NULL,
  `dept_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments`
  (`id`,`name_en`,`name_ne`,`description_en`,`description_ne`,`max_capacity`,`avg_service_time`,`current_load`,`is_active`) VALUES
(1,'General Medicine','सामान्य चिकित्सा','General health consultations and diagnoses','सामान्य स्वास्थ्य परामर्श र निदान',50,30,6,1),
(2,'Emergency','आकस्मिक','Emergency and critical care','आकस्मिक तथा गम्भीर उपचार',20,15,4,1),
(3,'Maternal Health','मातृ स्वास्थ्य','Pregnancy and maternal care','गर्भावस्था र मातृ हेरचाह',30,25,3,1),
(4,'Chronic Disease','दीर्घकालीन रोग','Diabetes, hypertension, respiratory disease','मधुमेह, रक्तचाप, श्वासप्रश्वास रोग',40,20,5,1),
(5,'Pediatrics','बाल रोग','Child health and vaccinations','बाल स्वास्थ्य र खोप',35,25,3,1),
(6,'Orthopedics','अस्थि रोग','Bone and joint disorders','हड्डी तथा जोर्नी सम्बन्धी विकार',25,30,2,1),
(7,'Cardiology','हृदय रोग','Heart and cardiovascular diseases','हृदय तथा नाडी रोग',20,35,3,1),
(8,'ENT','नाक, कान, घाँटी','Ear, Nose, and Throat','कान, नाक तथा घाँटी सम्बन्धी',30,20,1,1),
(9,'Dermatology','छाला रोग','Skin conditions and treatment','छाला सम्बन्धी उपचार',25,20,1,1),
(10,'Ophthalmology','आँखा रोग','Eye care and treatment','आँखा हेरचाह र उपचार',25,20,1,1);

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `name_en` varchar(100) DEFAULT NULL,
  `name_ne` varchar(100) DEFAULT NULL,
  `type` enum('Emergency','Referral','Education','Regular') DEFAULT 'Regular',
  `description_en` text DEFAULT NULL,
  `description_ne` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  -- legacy/compat columns
  `dept_id` int(11) DEFAULT NULL,
  `service_name` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services`
  (`id`,`name_en`,`name_ne`,`type`,`description_en`,`description_ne`,`is_active`) VALUES
(1,'Emergency Triage','आकस्मिक ट्राइएज','Emergency','Immediate assessment for emergencies','आकस्मिक अवस्थाको लागि तत्काल मूल्याङ्कन',1),
(2,'Chronic Disease Follow-up','दीर्घकालीन रोग अनुगमन','Regular','Regular check-ups for chronic diseases','दीर्घकालीन रोगको नियमित जाँच',1),
(3,'Maternal Check-up','मातृ स्वास्थ्य जाँच','Regular','Pregnancy and postnatal care','गर्भावस्था र प्रसवपछिको हेरचाह',1),
(4,'Vaccination','खोप सेवा','Regular','Immunization services','खोप सेवा',1),
(5,'Referral Service','रेफरल सेवा','Referral','Referral to specialized departments','विशेषज्ञ विभागमा रेफरल',1),
(6,'Health Education','स्वास्थ्य शिक्षा','Education','Health awareness and prevention tips','स्वास्थ्य सचेतना र रोकथाम सुझाव',1);

-- =====================================================================
-- 2. HOSPITALS + HOSPITAL MAPPING
-- =====================================================================

DROP TABLE IF EXISTS `hospital_locations`;
CREATE TABLE `hospital_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `hospital_name` varchar(150) NOT NULL,
  `district` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `ward` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL DEFAULT 0,
  `longitude` decimal(11,8) NOT NULL DEFAULT 0,
  `address` text DEFAULT NULL,
  `specialities` longtext DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` enum('Government','Private','Specialized','Non-Profit') DEFAULT 'Private',
  `description` text DEFAULT NULL,
  `emergency_24_7` tinyint(1) DEFAULT 0,
  `contact_email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hospital_locations`
  (`id`,`hospital_name`,`district`,`municipality`,`ward`,`latitude`,`longitude`,`address`,`specialities`,`phone`,`type`,`description`,`emergency_24_7`,`contact_email`,`is_active`) VALUES
(1,'Bhaktapur Hospital','Bhaktapur','Bhaktapur Municipality','Central',27.67190000,85.42200000,'Bhaktapur 44800, Nepal','["General Surgery","Emergency","Internal Medicine"]','+977-1-6610798','Government','Public district hospital providing multi-specialty basic services',1,'info@bhaktapurhospital.example',1),
(2,'Bhaktapur Cancer Hospital','Bhaktapur','Bhaktapur Municipality','Dudh Pati',27.67310000,85.42230000,'MCFC+7W8, Bhaktapur 44800','["Oncology","Cancer Care"]','+977-1-6611532','Specialized','Specialized oncology care and wards',0,'info@bhaktapurcancer.example',1),
(3,'Bhaktapur International Hospital','Bhaktapur','Madhyapur Thimi Municipality','General',27.66200000,85.35100000,'Madhyapur Thimi 44600','["General Medicine","Emergency","Surgery"]','+977-1-6618765','Private','Full-service private hospital',1,'info@bih.example',1),
(4,'Bhaktapur Model Hospital','Bhaktapur','Madhyapur Thimi Municipality','General',27.66400000,85.35200000,'Araniko Highway, Madhyapur Thimi 44800','["General Healthcare","Emergency","Pediatrics"]','+977-1-6615432','Private','General healthcare & emergency services',1,NULL,1),
(5,'Khwopa Hospital','Bhaktapur','Bhaktapur Municipality','9',27.67500000,85.43000000,'Ward-9, Garud Kundal Road, Bhaktapur 44800','["General Medicine","Emergency","Surgery"]','+977-1-6612000','Private','General hospital with comprehensive services',1,NULL,1),
(6,'KMC Hospital - Duwakot','Bhaktapur','Changunarayan Municipality','General',27.68000000,85.41000000,'Duwakot Rd, Bhaktapur 44800','["General Medicine","Emergency"]','+977-1-6610500','Private','General hospital with modern facilities',1,NULL,1),
(7,'Suryabinayak Municipal Hospital','Bhaktapur','Suryabinayak Municipality','General',27.69000000,85.44000000,'Araniko Highway, Bhaktapur 44800','["Community Health","General Medicine"]','+977-1-6614500','Government','Community general health services',1,NULL,1),
(8,'Khwopa Tilganga Eye Hospital','Bhaktapur','Bhaktapur Municipality','General',27.67200000,85.42250000,'Bhaktapur 44800','["Ophthalmology","Eye Care"]','+977-1-6611000','Specialized','Eye care and ophthalmology services',0,NULL,1),
(9,'Shahid Dharmabhakta National Transplant Center','Bhaktapur','Bhaktapur Municipality','General',27.67250000,85.42300000,'Bhaktapur 44800','["Transplant Surgery","Specialized Care"]','+977-1-6615000','Government','Transplant surgery & specialized care',0,NULL,1),
(10,'Dr. Iwamura Memorial Hospital','Bhaktapur','Bhaktapur Municipality','General',27.67100000,85.42400000,'Nagarkot Road, Bhaktapur 44800','["General Medicine","Emergency"]','+977-1-6615678','Private','Private general hospital',1,NULL,1),
(11,'Nagrik Hospital','Bhaktapur','Madhyapur Thimi Municipality','General',27.66300000,85.35150000,'Araniko Highway, Madhyapur Thimi 44800','["General Care","Emergency"]','+977-1-6612345','Private','General care services',1,NULL,1),
(12,'Bir Hospital','Kathmandu','Kathmandu Metropolitan City','Central',27.71720000,85.32400000,'Kathmandu 44600, Nepal','["General Medicine","Emergency","Surgery","Cardiology","Pediatrics"]','+977-1-4224881','Government','Government general hospital with emergency & multi-specialty services',1,'info@birhospital.example',1),
(13,'T.U. Teaching Hospital','Kathmandu','Kathmandu Metropolitan City','Maharajgunj',27.72700000,85.31800000,'Maharajgunj Sadak, Kathmandu 44600','["General Medicine","Surgery","Pediatrics","Internal Medicine"]','+977-1-4412801','Government','Teaching hospital with comprehensive services',1,'info@tuth.example',1),
(14,'Kathmandu Model Hospital','Kathmandu','Kathmandu Metropolitan City','Baneshwar',27.71400000,85.33500000,'Red Cross Marg, Kathmandu 44600','["General Care","Emergency","Surgery"]','+977-1-4228228','Private','General care and emergency services',1,NULL,1),
(15,'Nepal Police Hospital','Kathmandu','Kathmandu Metropolitan City','Chhauni',27.70800000,85.31200000,'Krishna Dhara Marg, Kathmandu 44600','["General Medicine","Emergency"]','+977-1-4215050','Government','Government hospital services',1,NULL,1),
(16,'Kantipur Hospital','Kathmandu','Kathmandu Metropolitan City','Silchok',27.72000000,85.34000000,'Shri Ganesh Marg, Kathmandu 44600','["Multi-specialty","Surgery","Cardiology","Orthopedics"]','+977-1-4261451','Private','Multi-specialty hospital',1,NULL,1),
(17,'Civil Service Hospital','Kathmandu','Kathmandu Metropolitan City','Minbhawan',27.72200000,85.33000000,'Minbhawan Marg, Kathmandu 44600','["General Medicine","Emergency"]','+977-1-4262058','Government','Government general health services',1,NULL,1),
(18,'Scheer Memorial Adventist Hospital','Kavre','Banepa Municipality','General',27.65500000,85.53800000,'JGMG+9W8, Banepa 45210','["General Medicine","Surgery","Pediatrics"]','+977-1-6620099','Private','General hospital with comprehensive services',1,NULL,1),
(19,'Satya Sai Hospital','Kavre','Banepa Municipality','General',27.65700000,85.54000000,'Araniko Highway, Banepa 45210','["Multi-specialty","General Medicine","Surgery"]','+977-1-6620456','Private','Multi-specialty hospital services',1,NULL,1),
(20,'HRDC - Hospital & Rehabilitation Center','Kavre','Banepa Municipality','11',27.65800000,85.54200000,'Ugratara Janagal, Banepa-11','["Pediatrics","Rehabilitation","Child Care"]','+977-1-6620789','Specialized','Rehabilitation & pediatric focus hospital',0,NULL,1),
(21,'K.B. Hospital Pvt. Ltd','Kavre','Banepa Municipality','General',27.65900000,85.54400000,'Prabesh Marga, Banepa 45210','["General Care","Emergency"]','+977-1-6621000','Private','General care services',1,NULL,1),
(22,'Reiyukai Eiko Masunaga Eye Hospital','Kavre','Banepa Municipality','General',27.66000000,85.54600000,'Banepa 45210','["Ophthalmology","Eye Care"]','+977-1-6621234','Specialized','Eye care and ophthalmology services',0,NULL,1),
(23,'Jujubhai Memorial Health Service','Kavre','Banepa Municipality','General',27.66100000,85.54800000,'Banepa 45210','["General Health","Community Care"]','+977-1-6621567','Non-Profit','General health services',1,NULL,1),
(24,'Big Care Hospital','Kavre','Banepa Municipality','10',27.66200000,85.55000000,'Janagal, Banepa-10','["General Care","Emergency"]','+977-1-6621890','Private','General care services',1,NULL,1);

DROP TABLE IF EXISTS `hospital_departments`;
CREATE TABLE `hospital_departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `hospital_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `max_tokens_per_day` int(11) DEFAULT 50,
  `current_daily_tokens` int(11) DEFAULT 0,
  `available` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hospital_departments` (`hospital_id`,`department_id`,`max_tokens_per_day`,`current_daily_tokens`,`available`,`is_active`) VALUES
(12,1,50,6,1,1),(12,2,20,4,1,1),(12,3,30,3,1,1),(12,4,40,3,1,1),(12,5,35,2,1,1),(12,6,25,1,1,1),(12,7,20,2,1,1),
(13,1,50,4,1,1),(13,2,20,2,1,1),(13,3,30,2,1,1),(13,4,40,2,1,1),(13,5,35,2,1,1),(13,7,20,1,1,1),
(16,1,40,3,1,1),(16,4,30,2,1,1),(16,6,25,2,1,1),(16,7,20,2,1,1),(16,10,25,1,1,1),
(1,1,40,3,1,1),(1,2,20,2,1,1),(1,6,20,2,1,1),
(18,1,40,2,1,1),(18,5,30,2,1,1);

-- =====================================================================
-- 3. PEOPLE: users, admins, staff
-- =====================================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `phone_number` varchar(20) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `ward` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `mpin` varchar(10) DEFAULT NULL,
  `old_mpin` varchar(10) DEFAULT NULL,
  `mpin_generated_at` timestamp NULL DEFAULT NULL,
  `is_pregnant` tinyint(1) DEFAULT 0,
  `chronic_diseases` longtext DEFAULT NULL,
  -- compat columns used by the older admin controllers
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `language` varchar(10) DEFAULT 'en',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone_verified` tinyint(1) DEFAULT 0,
  `avatar_color` varchar(20) DEFAULT 'primary',
  `last_booking_date` timestamp NULL DEFAULT NULL,
  `total_bookings` int(11) DEFAULT 0,
  `blood_type` varchar(10) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users`
  (`id`,`phone_number`,`full_name`,`age`,`gender`,`email`,`district`,`municipality`,`ward`,`mpin`,`is_pregnant`,`chronic_diseases`,`name`,`phone`,`language`,`phone_verified`,`avatar_color`,`total_bookings`,`blood_type`,`allergies`,`emergency_contact`,`emergency_contact_name`) VALUES
(1,'9803962360','Aarav Sharma',35,'Male','aarav.sharma@example.np','Kathmandu','Kathmandu Metropolitan City','5','3736',0,'["Diabetes"]','Aarav Sharma','9803962360','en',1,'info',7,'O+','Penicillin','9841234567','Sunita Sharma (Wife)'),
(2,'9844634579','Priya Thapa',28,'Female','priya.thapa@example.np','Bhaktapur','Bhaktapur Municipality','6','9599',1,NULL,'Priya Thapa','9844634579','ne',1,'primary',3,'A+',NULL,'9845551234','Bikash Thapa (Husband)'),
(3,'9812345678','Bikash Gurung',42,'Male','bikash.gurung@example.np','Kathmandu','Kathmandu Metropolitan City','12','1245',0,'["Hypertension"]','Bikash Gurung','9812345678','en',1,'success',4,'B+',NULL,'9811112233','Kamala Gurung (Mother)'),
(4,'9856789012','Sunita Rai',31,'Female','sunita.rai@example.np','Lalitpur','Lalitpur Metropolitan City','4','7788',0,NULL,'Sunita Rai','9856789012','ne',1,'warning',2,'AB+','Ibuprofen','9850001122','Hari Rai (Father)'),
(5,'9861112233','Ramesh Karki',58,'Male','ramesh.karki@example.np','Kathmandu','Kathmandu Metropolitan City','9','4455',0,'["Diabetes","Hypertension"]','Ramesh Karki','9861112233','en',1,'danger',6,'O-',NULL,'9862223344','Gita Karki (Wife)'),
(6,'9872223344','Anita Maharjan',24,'Female','anita.maharjan@example.np','Bhaktapur','Madhyapur Thimi Municipality','11','3322',1,NULL,'Anita Maharjan','9872223344','ne',1,'info',1,'A-',NULL,'9846667788','Suman Maharjan (Husband)'),
(7,'9883334455','Deepak Shrestha',47,'Male',NULL,'Kavre','Banepa Municipality','3','9900',0,'["COPD"]','Deepak Shrestha','9883334455','en',0,'primary',2,'B-',NULL,'9847778899','Sarita Shrestha (Sister)'),
(8,'9894445566','Manisha Tamang',36,'Female',NULL,'Kathmandu','Budhanilkantha Municipality','7','6644',0,NULL,'Manisha Tamang','9894445566','ne',0,'success',1,'O+',NULL,'9848889900','Dawa Tamang (Husband)'),
(9,'9777770001','Nabin Demo Patient',30,'Male','nabin.demo@example.np','Kathmandu','Kathmandu Metropolitan City','7','3684',0,NULL,'Nabin Demo Patient','9777770001','en',1,'primary',0,'A+',NULL,'9841230000','Demo Emergency Contact');

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('SuperAdmin','Admin','Officer','Staff','HospitalAdmin') DEFAULT 'Staff',
  `department_id` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `is_hospital_admin` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins`
  (`id`,`username`,`password_hash`,`full_name`,`email`,`role`,`department_id`,`hospital_id`,`is_hospital_admin`,`is_active`) VALUES
(1,'admin','$2y$10$4pdgMtyHd5XjRIyC3PqXFOuQscCESd15A31.PZxapripNl4kgTAA2','Super Admin','admin@smarthealth.local','SuperAdmin',NULL,NULL,0,1),
(2,'superadmin','$2y$10$4pdgMtyHd5XjRIyC3PqXFOuQscCESd15A31.PZxapripNl4kgTAA2','National Health Office','superadmin@smarthealth.local','SuperAdmin',NULL,NULL,0,1),
(3,'bir_admin','$2y$10$dkgEsJo5bWYc4gN5sH1G7.m2KPff/7ASpVlw9rnIXIvpA9xDo6pZ.','Dr. Ramesh Kumar','bir.admin@smarthealth.local','HospitalAdmin',1,12,1,1),
(4,'patan_admin','$2y$10$dkgEsJo5bWYc4gN5sH1G7.m2KPff/7ASpVlw9rnIXIvpA9xDo6pZ.','Dr. Sarita Basnet','patan.admin@smarthealth.local','HospitalAdmin',2,13,1,1),
(5,'bhaktapur_admin','$2y$10$dkgEsJo5bWYc4gN5sH1G7.m2KPff/7ASpVlw9rnIXIvpA9xDo6pZ.','Hari Prasad Neupane','hosp.admin@smarthealth.local','HospitalAdmin',1,1,1,1),
(6,'medicine_officer','$2y$10$JTJFhq4UCdGcVZgOLdo.C.JHdZeal7WMpXoT0/.SZ7vgGt0EDZK2K','Officer Nabin Rai','officer@smarthealth.local','Officer',1,NULL,0,1);

DROP TABLE IF EXISTS `hospital_staff`;
CREATE TABLE `hospital_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `hospital_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `status` enum('Active','Inactive','Leave') DEFAULT 'Active',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `hospital_staff` (`hospital_id`,`name`,`position`,`department_id`,`email`,`phone`,`admin_id`,`status`,`is_active`) VALUES
(12,'Dr. Anil Kumar','Senior Physician',1,'anil.kumar@bir.example','9841001001',3,'Active',1),
(12,'Nurse Maya Lama','Head Nurse',1,'maya.lama@bir.example','9841001002',3,'Active',1),
(12,'Dr. Sujan Raj','Emergency Officer',2,'sujan.raj@bir.example','9841001003',3,'Active',1),
(12,'Dr. Prakash Sharma','Cardiologist',7,'prakash.sharma@bir.example','9841001004',3,'Active',1),
(12,'Rita Karki','Receptionist',NULL,'rita.karki@bir.example','9841001005',NULL,'Active',1),
(13,'Dr. Bikram Poudel','Orthopedic Surgeon',6,'bikram.poudel@tuth.example','9841002001',4,'Active',1),
(13,'Dr. Ravi Nath','Internal Medicine',4,'ravi.nath@tuth.example','9841002002',4,'Active',1),
(13,'Nurse Sita Gurung','Staff Nurse',3,'sita.gurung@tuth.example','9841002003',4,'Leave',1),
(16,'Dr. Kamal Adhikari','Cardiologist',7,'kamal.adhikari@kantipur.example','9841003001',NULL,'Active',1),
(1,'Dr. Nirmala Shrestha','Medical Officer',1,'nirmala@bhaktapur.example','9841004001',5,'Active',1);

-- =====================================================================
-- 4. QUEUE / TOKENS
-- =====================================================================

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `user_district` varchar(100) DEFAULT NULL,
  `user_municipality` varchar(100) DEFAULT NULL,
  `user_ward` varchar(50) DEFAULT NULL,
  `token_number` varchar(30) NOT NULL,
  `priority` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `triage_reason` longtext DEFAULT NULL,
  `status` enum('Active','Called','Completed','Missed','Rescheduled','Pending','Confirmed','Cancelled') DEFAULT 'Active',
  `estimated_wait_time` int(11) DEFAULT NULL,
  `is_emergency` tinyint(1) DEFAULT 0,
  `is_chronic_followup` tinyint(1) DEFAULT 0,
  -- compat / extra columns
  `booking_type` varchar(50) DEFAULT NULL,
  `triage_type` varchar(30) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_slot_id` int(11) DEFAULT NULL,
  `time_window_start` time DEFAULT NULL,
  `time_window_end` time DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `called_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `missed_at` timestamp NULL DEFAULT NULL,
  `missed_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Active queue for TODAY (so dashboards are always populated)
INSERT INTO `tokens`
  (`id`,`user_id`,`department_id`,`hospital_id`,`user_district`,`user_municipality`,`user_ward`,`token_number`,`priority`,`triage_reason`,`status`,`estimated_wait_time`,`is_emergency`,`is_chronic_followup`,`created_at`) VALUES
(1,3,1,12,'Kathmandu','Kathmandu Metropolitan City','12','1220260101001','Normal','{"full_name":"Bikash Gurung","have_fever":true,"fever_days":2,"difficulty_breathing":false,"emergency_signs":false,"additional_notes":"Fever and headache"}','Active',30,0,0,NOW() - INTERVAL 55 MINUTE),
(2,5,4,12,'Kathmandu','Kathmandu Metropolitan City','9','1220260101002','Chronic','{"full_name":"Ramesh Karki","chronic_disease":true,"chronic_disease_names":["Diabetes","Hypertension"]}','Active',20,0,1,NOW() - INTERVAL 40 MINUTE),
(3,6,2,12,'Bhaktapur','Madhyapur Thimi Municipality','11','1220260101003','Emergency','{"full_name":"Anita Maharjan","emergency_signs":true,"are_pregnant":true}','Active',2,1,0,NOW() - INTERVAL 20 MINUTE),
(4,2,3,12,'Bhaktapur','Bhaktapur Municipality','6','1220260101004','Priority','{"full_name":"Priya Thapa","are_pregnant":true}','Called',15,0,0,NOW() - INTERVAL 70 MINUTE),
(5,4,1,13,'Lalitpur','Lalitpur Metropolitan City','4','1320260101001','Normal','{"full_name":"Sunita Rai","have_fever":false}','Active',10,0,0,NOW() - INTERVAL 15 MINUTE),
(6,7,4,13,'Kavre','Banepa Municipality','3','1320260101002','Chronic','{"full_name":"Deepak Shrestha","chronic_disease":true,"chronic_disease_names":["COPD"]}','Active',40,0,1,NOW() - INTERVAL 90 MINUTE),
(7,8,5,16,'Kathmandu','Budhanilkantha Municipality','7','1620260101001','Normal','{"full_name":"Manisha Tamang"}','Active',5,0,0,NOW() - INTERVAL 10 MINUTE),
(8,1,7,16,'Kathmandu','Kathmandu Metropolitan City','5','1620260101002','Priority','{"full_name":"Aarav Sharma","difficulty_breathing":true}','Active',25,0,0,NOW() - INTERVAL 30 MINUTE);

-- Historical / completed tokens (for reports & history)
INSERT INTO `tokens`
  (`id`,`user_id`,`department_id`,`hospital_id`,`user_district`,`user_municipality`,`user_ward`,`token_number`,`priority`,`triage_reason`,`status`,`estimated_wait_time`,`is_emergency`,`is_chronic_followup`,`created_at`,`completed_at`) VALUES
(9,1,1,12,'Kathmandu','Kathmandu Metropolitan City','5','1220251214001','Normal','{"full_name":"Aarav Sharma","have_fever":true}','Completed',15,0,0,'2025-12-14 08:10:00','2025-12-14 09:05:00'),
(10,1,4,12,'Kathmandu','Kathmandu Metropolitan City','5','1220251229002','Chronic','{"full_name":"Aarav Sharma","chronic_disease":true}','Completed',20,0,1,'2025-12-29 09:00:00','2025-12-29 09:30:00'),
(11,1,1,12,'Kathmandu','Kathmandu Metropolitan City','5','1220260113001','Priority','{"full_name":"Aarav Sharma","difficulty_breathing":true}','Completed',10,0,0,'2026-01-13 10:00:00','2026-01-13 10:40:00'),
(12,1,6,16,'Kathmandu','Kathmandu Metropolitan City','5','1620260129001','Priority','{"full_name":"Aarav Sharma","any_injury":true}','Completed',5,0,0,'2026-01-29 11:00:00','2026-01-29 11:25:00'),
(13,5,4,12,'Kathmandu','Kathmandu Metropolitan City','9','1220260205001','Chronic','{"full_name":"Ramesh Karki","chronic_disease":true}','Completed',15,0,1,'2026-02-05 08:30:00','2026-02-05 09:10:00'),
(14,3,1,12,'Kathmandu','Kathmandu Metropolitan City','12','1220260208001','Normal','{"full_name":"Bikash Gurung"}','Missed',0,0,0,'2026-02-08 09:15:00',NULL),
(15,4,1,13,'Lalitpur','Lalitpur Metropolitan City','4','1320260207001','Normal','{"full_name":"Sunita Rai"}','Missed',0,0,0,'2026-02-07 10:00:00',NULL);

-- =====================================================================
-- 5. HEALTH / TRACKING TABLES
-- =====================================================================

DROP TABLE IF EXISTS `chronic_diseases`;
CREATE TABLE `chronic_diseases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `disease_name` varchar(100) NOT NULL,
  `disease_code` varchar(20) DEFAULT NULL,
  `diagnosis_date` date DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `last_visit_date` date DEFAULT NULL,
  `medications` longtext DEFAULT NULL,
  `doctor_notes` text DEFAULT NULL,
  `status` enum('Active','Resolved','Suspended','Controlled','In Progress') DEFAULT 'Active',
  -- compat columns
  `disease_type` varchar(100) DEFAULT NULL,
  `last_visit` date DEFAULT NULL,
  `next_followup` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `medication` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `chronic_diseases`
  (`id`,`user_id`,`disease_name`,`disease_code`,`diagnosis_date`,`next_followup_date`,`last_visit_date`,`medications`,`doctor_notes`,`status`) VALUES
(1,1,'Diabetes Type 2','E11','2024-11-02',DATE_ADD(CURDATE(), INTERVAL 12 DAY),DATE_SUB(CURDATE(), INTERVAL 18 DAY),'[{"medicine":"Metformin","dosage":"500mg","frequency":"Twice daily"}]','Blood glucose within range. Continue medication and diet control.','Active'),
(2,1,'Essential Hypertension','I10','2025-01-16',DATE_ADD(CURDATE(), INTERVAL 5 DAY),DATE_SUB(CURDATE(), INTERVAL 25 DAY),'[{"medicine":"Lisinopril","dosage":"5mg","frequency":"Once daily"}]','BP slightly elevated. Reduce salt intake.','Active'),
(3,3,'Essential Hypertension','I10','2025-05-10',DATE_SUB(CURDATE(), INTERVAL 3 DAY),DATE_SUB(CURDATE(), INTERVAL 33 DAY),'[{"medicine":"Amlodipine","dosage":"5mg","frequency":"Once daily"}]','Follow-up overdue. Contact patient.','Active'),
(4,5,'Diabetes Type 2','E11','2023-08-21',DATE_ADD(CURDATE(), INTERVAL 20 DAY),DATE_SUB(CURDATE(), INTERVAL 10 DAY),'[{"medicine":"Metformin","dosage":"500mg","frequency":"Twice daily"}]','Stable. Continue same regimen.','Active'),
(5,7,'COPD - Mild','J44','2025-06-15',DATE_ADD(CURDATE(), INTERVAL 8 DAY),DATE_SUB(CURDATE(), INTERVAL 22 DAY),'[{"medicine":"Salbutamol","dosage":"100mcg","frequency":"As needed"}]','Advised to quit smoking. Breathing exercises prescribed.','In Progress');

DROP TABLE IF EXISTS `maternal_health`;
CREATE TABLE `maternal_health` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `pregnancy_status` enum('Pregnant','Post-Partum','Not Pregnant') DEFAULT 'Pregnant',
  `expected_due_date` date DEFAULT NULL,
  `last_menstrual_period` date DEFAULT NULL,
  `antenatal_visits_completed` int(11) DEFAULT 0,
  `next_antenatal_date` date DEFAULT NULL,
  `vaccinations_needed` longtext DEFAULT NULL,
  `last_checkup_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_high_risk` tinyint(1) DEFAULT 0,
  -- compat columns for API
  `due_date` date DEFAULT NULL,
  `lmp_date` date DEFAULT NULL,
  `antenatal_visits` text DEFAULT NULL,
  `vaccinations` text DEFAULT NULL,
  `warning_signs` text DEFAULT NULL,
  `last_antenatal_visit` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `maternal_health`
  (`id`,`user_id`,`pregnancy_status`,`expected_due_date`,`last_menstrual_period`,`antenatal_visits_completed`,`next_antenatal_date`,`vaccinations_needed`,`last_checkup_date`,`notes`,`is_high_risk`,`due_date`,`lmp_date`,`antenatal_visits`,`vaccinations`,`warning_signs`,`last_antenatal_visit`,`status`) VALUES
(1,2,'Pregnant',DATE_ADD(CURDATE(), INTERVAL 96 DAY),DATE_SUB(CURDATE(), INTERVAL 184 DAY),3,DATE_ADD(CURDATE(), INTERVAL 12 DAY),'["Td Vaccine (2nd dose)"]',DATE_SUB(CURDATE(), INTERVAL 16 DAY),'Normal pregnancy. Continue routine antenatal visits.',0,DATE_ADD(CURDATE(), INTERVAL 96 DAY),DATE_SUB(CURDATE(), INTERVAL 184 DAY),'["Visit 1","Visit 2","Visit 3"]','["Td (1st dose)"]','[]',DATE_SUB(CURDATE(), INTERVAL 16 DAY),'Active'),
(2,6,'Pregnant',DATE_ADD(CURDATE(), INTERVAL 150 DAY),DATE_SUB(CURDATE(), INTERVAL 130 DAY),2,DATE_ADD(CURDATE(), INTERVAL 20 DAY),'["Td Vaccine (2nd dose)","Iron & Folic Acid"]',DATE_SUB(CURDATE(), INTERVAL 10 DAY),'Mild anaemia noted. Iron supplements prescribed.',1,DATE_ADD(CURDATE(), INTERVAL 150 DAY),DATE_SUB(CURDATE(), INTERVAL 130 DAY),'["Visit 1","Visit 2"]','["Td (1st dose)"]','["Mild anaemia"]',DATE_SUB(CURDATE(), INTERVAL 10 DAY),'Active');

DROP TABLE IF EXISTS `health_assessments`;
CREATE TABLE `health_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `assessment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `has_fever` tinyint(1) DEFAULT 0,
  `fever_days` int(11) DEFAULT 0,
  `difficulty_breathing` tinyint(1) DEFAULT 0,
  `has_injury` tinyint(1) DEFAULT 0,
  `injury_severity` enum('Mild','Moderate','Severe') DEFAULT NULL,
  `is_pregnant` tinyint(1) DEFAULT 0,
  `has_chronic_disease` tinyint(1) DEFAULT 0,
  `chronic_disease_types` longtext DEFAULT NULL,
  `has_emergency_signs` tinyint(1) DEFAULT 0,
  `additional_notes` text DEFAULT NULL,
  `assessment_district` varchar(100) DEFAULT NULL,
  `assessment_municipality` varchar(100) DEFAULT NULL,
  `assessment_ward` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `status` enum('Active','Completed','Closed') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `health_assessments`
  (`user_id`,`token_id`,`has_fever`,`fever_days`,`difficulty_breathing`,`has_injury`,`is_pregnant`,`has_chronic_disease`,`chronic_disease_types`,`has_emergency_signs`,`additional_notes`,`assessment_district`,`assessment_municipality`,`assessment_ward`,`department_id`,`hospital_id`,`status`) VALUES
(1,9,1,4,0,0,0,0,NULL,0,'Common cold with mild fever.','Kathmandu','Kathmandu Metropolitan City','5',1,12,'Completed'),
(1,10,0,0,0,0,0,1,'["Diabetes"]',0,'Routine diabetes follow-up.','Kathmandu','Kathmandu Metropolitan City','5',4,12,'Completed'),
(1,11,0,0,1,0,0,1,'["COPD"]',0,'Mild respiratory discomfort.','Kathmandu','Kathmandu Metropolitan City','5',1,12,'Completed'),
(1,12,0,0,0,1,0,0,NULL,0,'Wrist sprain from a fall.','Kathmandu','Kathmandu Metropolitan City','5',6,16,'Completed'),
(3,1,1,2,0,0,0,0,NULL,0,'Fever and headache.','Kathmandu','Kathmandu Metropolitan City','12',1,12,'Active'),
(6,3,0,0,0,0,1,0,NULL,1,'Emergency visit - pregnant with complications.','Bhaktapur','Madhyapur Thimi Municipality','11',2,12,'Active');

DROP TABLE IF EXISTS `health_records`;
CREATE TABLE `health_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `symptoms` longtext DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `health_records`
  (`user_id`,`token_id`,`visit_date`,`symptoms`,`diagnosis`,`treatment_plan`,`doctor_name`,`department_id`,`notes`) VALUES
(1,9,'2025-12-14','["Fever","Cough","Runny nose"]','Common Cold - Viral Infection','Rest, fluids, Paracetamol 500mg twice daily','Dr. Anil Kumar',1,'Recovering well.'),
(1,10,'2025-12-29','["High blood pressure","Mild headache"]','Essential Hypertension Stage 1','Lisinopril 5mg once daily, salt reduction','Dr. Prakash Sharma',7,'BP 142/90. Monitor regularly.'),
(1,11,'2026-01-13','["Fever","Cough","Difficulty breathing"]','Bronchitis (Viral)','Oseltamivir, bed rest for 5 days','Dr. Ravi Nath',1,'Chest sounds clear.'),
(1,12,'2026-01-29','["Right wrist pain","Swelling"]','Grade 1 Wrist Sprain','Ice, compression bandage, Ibuprofen','Dr. Bikram Poudel',6,'Avoid heavy lifting.'),
(3,1,'2026-02-10','["Fever","Headache"]','Acute Viral Illness','Antipyretics, fluids, rest','Dr. Anil Kumar',1,'Monitor temperature.'),
(5,13,'2026-02-05','["Fatigue","Blood sugar review"]','Diabetes Type 2 - Routine Check','Continue Metformin, diet modification','Dr. Ravi Nath',4,'Sugar controlled.');

DROP TABLE IF EXISTS `booking_history`;
CREATE TABLE `booking_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `visited_date` timestamp NULL DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `status` enum('Pending','Visited','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `booking_history`
  (`user_id`,`token_id`,`department_id`,`hospital_id`,`booking_date`,`visited_date`,`doctor_name`,`diagnosis`,`treatment`,`follow_up_required`,`follow_up_date`,`notes`,`rating`,`feedback`,`status`) VALUES
(1,9,1,12,'2025-12-14 08:10:00','2025-12-14 09:05:00','Dr. Anil Kumar','Common Cold and Minor Fever','Paracetamol, rest and fluids',1,DATE_ADD(CURDATE(), INTERVAL 14 DAY),'Follow-up done.',4,'Good service.','Completed'),
(1,10,4,12,'2025-12-29 09:00:00','2025-12-29 09:30:00','Dr. Prakash Sharma','Hypertension Stage 1','Lisinopril 5mg daily',1,DATE_ADD(CURDATE(), INTERVAL 5 DAY),'Compliant with medication.',5,'Professional doctor.','Completed'),
(1,13,4,12,'2026-02-05 08:30:00','2026-02-05 09:10:00','Dr. Ravi Nath','Diabetes Routine Check','Continue Metformin',1,DATE_ADD(CURDATE(), INTERVAL 20 DAY),'Sugar controlled.',NULL,NULL,'Visited'),
(5,13,4,12,'2026-02-05 08:35:00','2026-02-05 09:12:00','Dr. Ravi Nath','Type 2 Diabetes','Diet + Metformin',0,NULL,'Stable.',NULL,NULL,'Visited');

DROP TABLE IF EXISTS `referrals`;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `from_department_id` int(11) DEFAULT NULL,
  `to_department_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `referred_date` date DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  -- compat columns
  `from_hospital` varchar(150) DEFAULT NULL,
  `to_hospital` varchar(150) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `referrals`
  (`user_id`,`from_department_id`,`to_department_id`,`reason`,`referred_date`,`is_completed`,`notes`,`from_hospital`,`to_hospital`,`status`) VALUES
(1,1,7,'Chest pain evaluation required.','2026-02-05',0,'Referred for cardiology consultation.','Bir Hospital','Bir Hospital','Pending'),
(3,1,4,'Blood pressure not controlled.','2026-02-06',0,'Chronic care referral.','Bir Hospital','Bir Hospital','Approved'),
(7,1,2,'Acute breathing difficulty.','2026-02-07',1,'Emergency referral handled.','T.U. Teaching Hospital','T.U. Teaching Hospital','Completed');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `type` enum('Token','Chronic','Maternal','Appointment','System','FOLLOWUP','FOLLOWUP_UPDATED') DEFAULT 'System',
  `message_en` text DEFAULT NULL,
  `message_ne` text DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_sms` tinyint(1) DEFAULT 0,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivery_status` enum('Pending','Sent','Failed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications`
  (`user_id`,`type`,`message_en`,`message_ne`,`title`,`message`,`is_sms`,`is_sent`,`delivery_status`,`created_at`) VALUES
(1,'Token','Your token #1220260101001 is now active.','तपाईंको टोकन #1220260101001 सक्रिय भएको छ।','Token Active','Your token is active',0,0,'Pending',NOW() - INTERVAL 50 MINUTE),
(1,'Chronic','Diabetes follow-up is due in 12 days.','मधुमेह अनुगमन १२ दिनमा।','Follow-up Reminder','Diabetes follow-up due soon',1,1,'Sent',NOW() - INTERVAL 1 DAY),
(2,'Maternal','Your next antenatal visit is scheduled.','तपाईंको अर्को प्रसवपूर्व जाँच तय भएको छ।','Antenatal Reminder','Antenatal visit reminder',1,0,'Pending',NOW() - INTERVAL 6 HOUR),
(6,'Maternal','High-risk pregnancy - please visit soon.','उच्च जोखिम गर्भावस्था - चाँडै आउनुहोस्।','Maternal Alert','High risk pregnancy',1,0,'Pending',NOW() - INTERVAL 3 HOUR),
(5,'Chronic','BP follow-up reminder.','रक्तचाप अनुगमन स्मरण।','Follow-up Reminder','BP follow-up',0,0,'Pending',NOW() - INTERVAL 2 DAY);

-- =====================================================================
-- 6. OTP / AUTH / SETTINGS
-- =====================================================================

DROP TABLE IF EXISTS `otp_sessions`;
CREATE TABLE `otp_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `phone_number` varchar(20) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `mpin` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `booking_data` longtext DEFAULT NULL,
  `status` enum('Pending','Verified','Expired','Used') DEFAULT 'Pending',
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','password','json') DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`setting_key`,`setting_value`,`setting_type`,`category`) VALUES
('sms_enabled','0','boolean','sms'),
('sms_provider','debug','text','sms'),
('sms_api_url','http://api.sparrowsms.com/v2/','text','sms'),
('sms_api_key','','password','sms'),
('sms_sender_id','SmartHealth','text','sms'),
('otp_expiry_minutes','10','number','security'),
('max_otp_attempts','3','number','security'),
('max_otp_send_attempts','5','number','security'),
('otp_resend_cooldown','60','number','security'),
('mpin_auto_generate','1','boolean','security'),
('mpin_send_via_sms','0','boolean','sms');

-- =====================================================================
-- 7. APPOINTMENTS & OFFLINE / ASSISTED BOOKING
-- =====================================================================

DROP TABLE IF EXISTS `appointment_slots`;
CREATE TABLE `appointment_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `hospital_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `slot_date` date NOT NULL,
  `time_window_start` time NOT NULL,
  `time_window_end` time NOT NULL,
  `slot_type` varchar(30) DEFAULT NULL,
  `max_capacity` int(11) DEFAULT 10,
  `booked_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- next 14 days for a few hospital/department pairs
INSERT INTO `appointment_slots` (`hospital_id`,`department_id`,`slot_date`,`time_window_start`,`time_window_end`,`slot_type`,`max_capacity`,`booked_count`,`is_active`) VALUES
(12,1,CURDATE(),'08:00:00','12:00:00','Early Morning',15,6,1),
(12,1,CURDATE(),'14:00:00','17:00:00','Afternoon',12,3,1),
(12,1,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'08:00:00','12:00:00','Early Morning',15,2,1),
(12,1,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'14:00:00','17:00:00','Afternoon',12,0,1),
(12,1,DATE_ADD(CURDATE(),INTERVAL 2 DAY),'08:00:00','12:00:00','Early Morning',15,0,1),
(12,1,DATE_ADD(CURDATE(),INTERVAL 2 DAY),'14:00:00','17:00:00','Afternoon',12,0,1),
(12,2,CURDATE(),'08:00:00','12:00:00','Early Morning',10,4,1),
(12,2,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'08:00:00','12:00:00','Early Morning',10,1,1),
(12,4,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'14:00:00','17:00:00','Afternoon',12,0,1),
(12,7,DATE_ADD(CURDATE(),INTERVAL 2 DAY),'08:00:00','12:00:00','Early Morning',8,1,1),
(13,1,CURDATE(),'08:00:00','12:00:00','Early Morning',15,4,1),
(13,1,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'08:00:00','12:00:00','Early Morning',15,0,1),
(13,4,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'14:00:00','17:00:00','Afternoon',12,0,1),
(16,7,CURDATE(),'14:00:00','17:00:00','Afternoon',8,2,1),
(16,1,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'08:00:00','12:00:00','Early Morning',15,0,1);

DROP TABLE IF EXISTS `offline_bookings`;
CREATE TABLE `offline_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `phone_number` varchar(20) NOT NULL,
  `patient_name` varchar(100) DEFAULT NULL,
  `booked_by_staff_id` int(11) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `triage_classification` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `booking_mode` enum('Assisted','SMS') DEFAULT 'Assisted',
  `token_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Converted','Expired') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `offline_bookings` (`phone_number`,`patient_name`,`booked_by_staff_id`,`department_id`,`triage_classification`,`booking_mode`,`status`) VALUES
('9811112233','Ram Bahadur',3,1,'Normal','Assisted','Converted'),
('9822223344','Sita Karki',3,3,'Priority','Assisted','Pending'),
('9833334455','Hari Lama',NULL,2,'Emergency','SMS','Converted');

DROP TABLE IF EXISTS `assisted_bookings`;
CREATE TABLE `assisted_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `hospital_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `patient_phone` varchar(20) NOT NULL,
  `patient_age` int(11) DEFAULT NULL,
  `patient_gender` varchar(10) DEFAULT NULL,
  `triage_data` longtext DEFAULT NULL,
  `priority` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `booking_date` date NOT NULL,
  `booking_time` time DEFAULT NULL,
  `booked_by` int(11) DEFAULT NULL,
  `registered_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','Assigned','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `assisted_bookings`
  (`hospital_id`,`department_id`,`patient_name`,`patient_phone`,`patient_age`,`patient_gender`,`triage_data`,`priority`,`booking_date`,`booking_time`,`booked_by`,`registered_by`,`notes`,`status`) VALUES
(12,1,'Kamala Devi Yadav','9801112233',52,'Female','{"symptoms":"Fever and body ache"}','Normal',CURDATE(),'09:30:00',3,3,'Assisted by reception','Pending'),
(12,2,'Bishal Tamang','9802223344',19,'Male','{"symptoms":"Minor road accident injury"}','Priority',CURDATE(),'10:00:00',3,3,'Needs X-ray','Assigned'),
(12,4,'Gita Shrestha','9803334455',63,'Female','{"symptoms":"Diabetes follow-up"}','Chronic',DATE_ADD(CURDATE(),INTERVAL 1 DAY),'09:00:00',3,3,'Routine follow-up','Pending'),
(12,1,'Suman Maharjan','9804445566',34,'Male','{"symptoms":"General check-up"}','Normal',DATE_ADD(CURDATE(),INTERVAL 2 DAY),'11:00:00',3,3,NULL,'Pending'),
(13,6,'Dipesh Rai','9805556677',27,'Male','{"symptoms":"Sports injury to knee"}','Priority',CURDATE(),'14:30:00',4,4,'Ortho consult','Completed');

DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_logs` (`admin_id`,`action`,`token_id`,`details`) VALUES
(3,'CALL_TOKEN',4,'Token called for General Medicine'),
(3,'COMPLETE_TOKEN',9,'Token completed'),
(4,'CALL_TOKEN',6,'Token called for Chronic Disease');

DROP TABLE IF EXISTS `hospital_statistics`;
CREATE TABLE `hospital_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `hospital_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_tokens` int(11) DEFAULT 0,
  `completed_tokens` int(11) DEFAULT 0,
  `missed_tokens` int(11) DEFAULT 0,
  `emergency_tokens` int(11) DEFAULT 0,
  `assisted_bookings` int(11) DEFAULT 0,
  `total_patients` int(11) DEFAULT 0,
  `new_patients` int(11) DEFAULT 0,
  `avg_wait_time` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 30 days of rolling statistics for hospitals 12, 13, 16
INSERT INTO `hospital_statistics`
  (`hospital_id`,`date`,`total_tokens`,`completed_tokens`,`missed_tokens`,`emergency_tokens`,`assisted_bookings`,`total_patients`,`new_patients`,`avg_wait_time`)
SELECT h.id, DATE_SUB(CURDATE(), INTERVAL seq.n DAY),
  20 + (seq.n % 9), 14 + (seq.n % 6), seq.n % 4, seq.n % 3, 2 + (seq.n % 4), 18 + (seq.n % 7), seq.n % 3, 12 + (seq.n % 15)
FROM (SELECT 12 AS id UNION ALL SELECT 13 UNION ALL SELECT 16) h
JOIN (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
      UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
      UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29) seq;

-- =====================================================================
-- 8. MISC / LEGACY TABLES
-- =====================================================================

DROP TABLE IF EXISTS `symptom_hospital_mapping`;
CREATE TABLE `symptom_hospital_mapping` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `symptom_name` varchar(100) NOT NULL,
  `required_specialities` longtext NOT NULL,
  `priority_level` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `symptom_hospital_mapping` (`symptom_name`,`required_specialities`,`priority_level`) VALUES
('Fever','["General Medicine","Emergency","Internal Medicine"]','Normal'),
('Difficulty Breathing','["Emergency","Internal Medicine"]','Emergency'),
('Chest Pain','["Cardiology","Emergency"]','Emergency'),
('Injury','["Surgery","Emergency","Orthopedics"]','Priority'),
('Pregnancy Related','["Maternal Health","General Medicine"]','Priority'),
('Diabetes','["General Medicine","Internal Medicine"]','Chronic'),
('Hypertension','["Cardiology","General Medicine"]','Chronic'),
('Eye Problem','["Ophthalmology","General Medicine"]','Normal'),
('Child Health','["Pediatrics","General Medicine"]','Normal'),
('Bone/Joint Problem','["Orthopedics","Surgery"]','Priority');

DROP TABLE IF EXISTS `user_locations`;
CREATE TABLE `user_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `district` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `ward` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `address_text` text DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_locations` (`user_id`,`district`,`municipality`,`ward`,`is_primary`) VALUES
(1,'Kathmandu','Kathmandu Metropolitan City','5',1),
(2,'Bhaktapur','Bhaktapur Municipality','6',1),
(3,'Kathmandu','Kathmandu Metropolitan City','12',1);

DROP TABLE IF EXISTS `triage_responses`;
CREATE TABLE `triage_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  `user_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `has_fever` tinyint(1) DEFAULT 0,
  `fever_duration` int(11) DEFAULT NULL,
  `difficulty_breathing` tinyint(1) DEFAULT 0,
  `injury` tinyint(1) DEFAULT 0,
  `injury_severity` varchar(50) DEFAULT NULL,
  `pregnancy` tinyint(1) DEFAULT 0,
  `chronic_disease` tinyint(1) DEFAULT 0,
  `chronic_disease_names` longtext DEFAULT NULL,
  `emergency_signs` tinyint(1) DEFAULT 0,
  `additional_notes` text DEFAULT NULL,
  `assigned_priority` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 9. PRIMARY KEYS, INDEXES, AUTO_INCREMENT
-- =====================================================================

ALTER TABLE `admins` ADD UNIQUE KEY `username` (`username`), ADD KEY `department_id` (`department_id`), ADD KEY `hospital_id` (`hospital_id`);
ALTER TABLE `booking_history` ADD KEY `token_id` (`token_id`), ADD KEY `department_id` (`department_id`), ADD KEY `idx_user_id` (`user_id`), ADD KEY `idx_booking_date` (`booking_date`);
ALTER TABLE `chronic_diseases` ADD KEY `idx_user` (`user_id`), ADD KEY `idx_followup` (`next_followup_date`);
ALTER TABLE `departments` ADD KEY `idx_dept_name` (`dept_name`), ADD KEY `idx_hospital_id` (`hospital_id`);
ALTER TABLE `health_assessments` ADD KEY `department_id` (`department_id`), ADD KEY `idx_user_id` (`user_id`), ADD KEY `idx_token_id` (`token_id`), ADD KEY `idx_assessment_date` (`assessment_date`);
ALTER TABLE `health_records` ADD KEY `token_id` (`token_id`), ADD KEY `department_id` (`department_id`), ADD KEY `idx_user` (`user_id`), ADD KEY `idx_visit_date` (`visit_date`);
ALTER TABLE `hospital_locations` ADD UNIQUE KEY `unique_hospital` (`hospital_name`,`district`), ADD KEY `idx_district` (`district`), ADD KEY `idx_municipality` (`municipality`);
ALTER TABLE `hospital_departments` ADD UNIQUE KEY `unique_hospital_department` (`hospital_id`,`department_id`), ADD KEY `department_id` (`department_id`);
ALTER TABLE `hospital_staff` ADD KEY `hospital_id` (`hospital_id`), ADD KEY `department_id` (`department_id`), ADD KEY `admin_id` (`admin_id`);
ALTER TABLE `assisted_bookings` ADD KEY `hospital_id` (`hospital_id`), ADD KEY `department_id` (`department_id`), ADD KEY `booked_by` (`booked_by`), ADD KEY `idx_booking_date` (`booking_date`);
ALTER TABLE `admin_logs` ADD KEY `admin_id` (`admin_id`), ADD KEY `token_id` (`token_id`);
ALTER TABLE `hospital_statistics` ADD UNIQUE KEY `unique_hospital_date` (`hospital_id`,`date`), ADD KEY `idx_date` (`date`);
ALTER TABLE `appointment_slots` ADD UNIQUE KEY `unique_slot` (`hospital_id`,`department_id`,`slot_date`,`time_window_start`), ADD KEY `department_id` (`department_id`), ADD KEY `idx_slot_date` (`slot_date`);
ALTER TABLE `maternal_health` ADD KEY `idx_user` (`user_id`), ADD KEY `idx_due_date` (`expected_due_date`);
ALTER TABLE `notifications` ADD KEY `idx_user` (`user_id`), ADD KEY `idx_status` (`delivery_status`), ADD KEY `idx_notification_date` (`created_at`);
ALTER TABLE `offline_bookings` ADD KEY `booked_by_staff_id` (`booked_by_staff_id`), ADD KEY `department_id` (`department_id`), ADD KEY `token_id` (`token_id`);
ALTER TABLE `otp_sessions` ADD KEY `idx_phone` (`phone_number`), ADD KEY `idx_status` (`status`);
ALTER TABLE `referrals` ADD KEY `user_id` (`user_id`), ADD KEY `from_department_id` (`from_department_id`), ADD KEY `to_department_id` (`to_department_id`), ADD KEY `idx_status` (`status`);
ALTER TABLE `services` ADD KEY `idx_dept_id` (`dept_id`), ADD KEY `idx_service_name` (`service_name`);
ALTER TABLE `symptom_hospital_mapping` ADD UNIQUE KEY `unique_symptom` (`symptom_name`);
ALTER TABLE `system_settings` ADD UNIQUE KEY `setting_key` (`setting_key`), ADD KEY `idx_category` (`category`);
ALTER TABLE `tokens` ADD KEY `user_id` (`user_id`), ADD KEY `idx_status` (`status`), ADD KEY `idx_priority` (`priority`), ADD KEY `idx_department` (`department_id`), ADD KEY `idx_token_date` (`created_at`), ADD KEY `idx_hospital_id` (`hospital_id`), ADD KEY `idx_token_number` (`token_number`), ADD KEY `idx_appointment_slot` (`appointment_slot_id`);
ALTER TABLE `triage_responses` ADD KEY `user_id` (`user_id`), ADD KEY `token_id` (`token_id`);
ALTER TABLE `users` ADD UNIQUE KEY `phone_number` (`phone_number`), ADD KEY `idx_user_location` (`district`,`municipality`), ADD KEY `idx_name` (`name`), ADD KEY `idx_phone` (`phone`);
ALTER TABLE `user_locations` ADD KEY `idx_user_id` (`user_id`);

ALTER TABLE `admins` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `booking_history` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `chronic_diseases` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `departments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `health_assessments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `health_records` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `hospital_locations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
ALTER TABLE `hospital_departments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `hospital_staff` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `assisted_bookings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `admin_logs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `hospital_statistics` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `appointment_slots` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `maternal_health` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `notifications` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `offline_bookings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `otp_sessions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `referrals` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `services` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `symptom_hospital_mapping` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `system_settings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `tokens` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
ALTER TABLE `triage_responses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
ALTER TABLE `user_locations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- =====================================================================
-- 10. FOREIGN KEYS
-- =====================================================================

ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admins_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE SET NULL;

ALTER TABLE `booking_history`
  ADD CONSTRAINT `booking_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_history_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_history_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

ALTER TABLE `chronic_diseases`
  ADD CONSTRAINT `chronic_diseases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `health_assessments`
  ADD CONSTRAINT `health_assessments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_assessments_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_assessments_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_records_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

ALTER TABLE `hospital_departments`
  ADD CONSTRAINT `hd_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hd_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

ALTER TABLE `hospital_staff`
  ADD CONSTRAINT `hs_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hs_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hs_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

ALTER TABLE `assisted_bookings`
  ADD CONSTRAINT `ab_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ab_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ab_ibfk_3` FOREIGN KEY (`booked_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

ALTER TABLE `admin_logs`
  ADD CONSTRAINT `al_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `al_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL;

ALTER TABLE `hospital_statistics`
  ADD CONSTRAINT `hs_stat_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE CASCADE;

ALTER TABLE `appointment_slots`
  ADD CONSTRAINT `as_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `as_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

ALTER TABLE `maternal_health`
  ADD CONSTRAINT `maternal_health_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `offline_bookings`
  ADD CONSTRAINT `offline_bookings_ibfk_1` FOREIGN KEY (`booked_by_staff_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `offline_bookings_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offline_bookings_ibfk_3` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL;

ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`from_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

ALTER TABLE `tokens`
  ADD CONSTRAINT `fk_hospital_id` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tokens_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

ALTER TABLE `triage_responses`
  ADD CONSTRAINT `triage_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `triage_responses_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL;

ALTER TABLE `user_locations`
  ADD CONSTRAINT `user_locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- 11. COMPATIBILITY TRIGGERS
-- =====================================================================
-- The project contains code written against two slightly different
-- column-naming conventions. These BEFORE INSERT/UPDATE triggers keep
-- the canonical and the legacy/compat columns in sync so every code
-- path reads and writes consistent data.

DROP TRIGGER IF EXISTS `trg_users_bi`;
DROP TRIGGER IF EXISTS `trg_users_bu`;
DELIMITER //
CREATE TRIGGER `trg_users_bi` BEFORE INSERT ON `users` FOR EACH ROW
BEGIN
  IF NEW.name IS NULL THEN SET NEW.name = NEW.full_name; END IF;
  IF NEW.full_name IS NULL THEN SET NEW.full_name = NEW.name; END IF;
  IF NEW.phone IS NULL THEN SET NEW.phone = NEW.phone_number; END IF;
  IF NEW.phone_number IS NULL THEN SET NEW.phone_number = NEW.phone; END IF;
  IF NEW.language IS NULL THEN SET NEW.language = 'en'; END IF;
END//
CREATE TRIGGER `trg_users_bu` BEFORE UPDATE ON `users` FOR EACH ROW
BEGIN
  IF NEW.full_name <> OLD.full_name THEN SET NEW.name = NEW.full_name; END IF;
  IF NEW.name <> OLD.name THEN SET NEW.full_name = NEW.name; END IF;
  IF NEW.phone_number <> OLD.phone_number THEN SET NEW.phone = NEW.phone_number; END IF;
  IF NEW.phone <> OLD.phone THEN SET NEW.phone_number = NEW.phone; END IF;
END//

DROP TRIGGER IF EXISTS `trg_dept_bi`;
DROP TRIGGER IF EXISTS `trg_dept_bu`;
CREATE TRIGGER `trg_dept_bi` BEFORE INSERT ON `departments` FOR EACH ROW
BEGIN
  IF NEW.name_en IS NULL THEN SET NEW.name_en = NEW.dept_name; END IF;
  IF NEW.dept_name IS NULL THEN SET NEW.dept_name = NEW.name_en; END IF;
  IF NEW.name_ne IS NULL THEN SET NEW.name_ne = NEW.name_en; END IF;
  IF NEW.capacity IS NULL THEN SET NEW.capacity = NEW.max_capacity; END IF;
  IF NEW.max_capacity IS NULL THEN SET NEW.max_capacity = NEW.capacity; END IF;
END//
CREATE TRIGGER `trg_dept_bu` BEFORE UPDATE ON `departments` FOR EACH ROW
BEGIN
  IF NEW.name_en <> OLD.name_en THEN SET NEW.dept_name = NEW.name_en; END IF;
  IF NEW.dept_name <> OLD.dept_name THEN SET NEW.name_en = NEW.dept_name; END IF;
  IF NEW.capacity <> OLD.capacity THEN SET NEW.max_capacity = NEW.capacity; END IF;
  IF NEW.max_capacity <> OLD.max_capacity THEN SET NEW.capacity = NEW.max_capacity; END IF;
END//

DROP TRIGGER IF EXISTS `trg_services_bi`;
CREATE TRIGGER `trg_services_bi` BEFORE INSERT ON `services` FOR EACH ROW
BEGIN
  IF NEW.name_en IS NULL THEN SET NEW.name_en = NEW.service_name; END IF;
  IF NEW.service_name IS NULL THEN SET NEW.service_name = NEW.name_en; END IF;
  IF NEW.name_ne IS NULL THEN SET NEW.name_ne = NEW.name_en; END IF;
  IF NEW.description_en IS NULL THEN SET NEW.description_en = NEW.description; END IF;
  IF NEW.status IS NULL THEN SET NEW.status = 'Active'; END IF;
END//

DROP TRIGGER IF EXISTS `trg_chronic_bi`;
DROP TRIGGER IF EXISTS `trg_chronic_bu`;
CREATE TRIGGER `trg_chronic_bi` BEFORE INSERT ON `chronic_diseases` FOR EACH ROW
BEGIN
  IF NEW.disease_type IS NULL THEN SET NEW.disease_type = NEW.disease_name; END IF;
  IF NEW.disease_name IS NULL THEN SET NEW.disease_name = NEW.disease_type; END IF;
  IF NEW.next_followup IS NULL THEN SET NEW.next_followup = NEW.next_followup_date; END IF;
  IF NEW.next_followup_date IS NULL THEN SET NEW.next_followup_date = NEW.next_followup; END IF;
  IF NEW.last_visit IS NULL THEN SET NEW.last_visit = NEW.last_visit_date; END IF;
  IF NEW.last_visit_date IS NULL THEN SET NEW.last_visit_date = NEW.last_visit; END IF;
  IF NEW.notes IS NULL THEN SET NEW.notes = NEW.doctor_notes; END IF;
END//
CREATE TRIGGER `trg_chronic_bu` BEFORE UPDATE ON `chronic_diseases` FOR EACH ROW
BEGIN
  IF NEW.next_followup_date <> OLD.next_followup_date THEN SET NEW.next_followup = NEW.next_followup_date; END IF;
  IF NEW.next_followup <> OLD.next_followup THEN SET NEW.next_followup_date = NEW.next_followup; END IF;
  IF NEW.last_visit_date <> OLD.last_visit_date THEN SET NEW.last_visit = NEW.last_visit_date; END IF;
  IF NEW.last_visit <> OLD.last_visit THEN SET NEW.last_visit_date = NEW.last_visit; END IF;
END//

DROP TRIGGER IF EXISTS `trg_maternal_bi`;
DROP TRIGGER IF EXISTS `trg_maternal_bu`;
CREATE TRIGGER `trg_maternal_bi` BEFORE INSERT ON `maternal_health` FOR EACH ROW
BEGIN
  IF NEW.due_date IS NULL THEN SET NEW.due_date = NEW.expected_due_date; END IF;
  IF NEW.expected_due_date IS NULL THEN SET NEW.expected_due_date = NEW.due_date; END IF;
  IF NEW.lmp_date IS NULL THEN SET NEW.lmp_date = NEW.last_menstrual_period; END IF;
  IF NEW.last_menstrual_period IS NULL THEN SET NEW.last_menstrual_period = NEW.lmp_date; END IF;
END//
CREATE TRIGGER `trg_maternal_bu` BEFORE UPDATE ON `maternal_health` FOR EACH ROW
BEGIN
  IF NEW.expected_due_date <> OLD.expected_due_date THEN SET NEW.due_date = NEW.expected_due_date; END IF;
  IF NEW.due_date <> OLD.due_date THEN SET NEW.expected_due_date = NEW.due_date; END IF;
  IF NEW.last_menstrual_period <> OLD.last_menstrual_period THEN SET NEW.lmp_date = NEW.last_menstrual_period; END IF;
  IF NEW.lmp_date <> OLD.lmp_date THEN SET NEW.last_menstrual_period = NEW.lmp_date; END IF;
END//
DELIMITER ;

-- =====================================================================
-- 12. VIEWS
-- =====================================================================

DROP VIEW IF EXISTS `nearby_hospitals_view`;
CREATE OR REPLACE VIEW `nearby_hospitals_view` AS
SELECT h.id, h.hospital_name, h.district, h.municipality, h.ward,
       h.latitude, h.longitude, h.specialities, h.phone, h.type, h.description
FROM hospital_locations h
WHERE h.is_active = 1
ORDER BY h.district, h.municipality;

-- =====================================================================
-- 13. BACKFILL COMPAT COLUMNS FOR SEED ROWS
-- =====================================================================
-- (Seed rows were inserted before the sync triggers existed.)

UPDATE `users`
  SET `name` = COALESCE(`name`, `full_name`),
      `phone` = COALESCE(`phone`, `phone_number`),
      `language` = COALESCE(`language`, 'en');

UPDATE `departments`
  SET `dept_name` = COALESCE(`dept_name`, `name_en`),
      `capacity`  = COALESCE(`capacity`, `max_capacity`);

UPDATE `services`
  SET `service_name` = COALESCE(`service_name`, `name_en`),
      `description` = COALESCE(`description`, `description_en`);

UPDATE `chronic_diseases`
  SET `disease_type` = COALESCE(`disease_type`, `disease_name`),
      `next_followup` = COALESCE(`next_followup`, `next_followup_date`),
      `last_visit` = COALESCE(`last_visit`, `last_visit_date`),
      `notes` = COALESCE(`notes`, `doctor_notes`);

UPDATE `maternal_health`
  SET `due_date` = COALESCE(`due_date`, `expected_due_date`),
      `lmp_date` = COALESCE(`lmp_date`, `last_menstrual_period`);

-- =====================================================================
-- 14. CONTACT MESSAGES (patient "Contact Us" form)
-- =====================================================================

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('New','Read','Archived') DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- END OF SCHEMA
-- =====================================================================

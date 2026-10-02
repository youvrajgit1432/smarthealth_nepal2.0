-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 08:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smarthealth`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('SuperAdmin','Admin','Officer','Staff') DEFAULT 'Staff',
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `department_id`, `is_active`, `last_login`, `created_at`) VALUES
(3, 'admin', '$2y$10$zY1Y9DbKoNV8dJYyHEiO0eytsyG756ZHNsl6STEqW6cGB8BIHRPhq', 'Super Admin', 'admin@smarthealth.local', 'SuperAdmin', NULL, 1, '2026-02-12 06:56:36', '2026-02-12 02:39:41'),
(4, 'medicine_admin', '$2y$10$WOc2oQ3PD0PdiAisSjL.rOi8dFtxAQ9RnJC4EGRHjPiF3LqWJBrsu', 'Dr. Ramesh Kumar', 'ramesh@smarthealth.local', 'Admin', 1, 1, NULL, '2026-02-12 02:39:41'),
(5, 'emergency_staff', '$2y$10$cokTPQt3o1VgafJA69mge.G3wYTMnk7fds/BWABDioNU3GxHHvvj6', 'Hari Prasad', 'hari@smarthealth.local', 'Staff', 2, 1, NULL, '2026-02-12 02:39:41'),
(6, 'maternal_officer', '$2y$10$d6DI5SSeFvLqb9eqmOqFw.qgF046iap9svEy/aTeO.vxMAgauC7Ju', 'Sita Sharma', 'sita@smarthealth.local', 'Officer', 3, 1, NULL, '2026-02-12 02:39:41');

-- --------------------------------------------------------

--
-- Table structure for table `booking_history`
--

CREATE TABLE `booking_history` (
  `id` int(11) NOT NULL,
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
  `rating` int(11) DEFAULT NULL COMMENT '1-5 star rating',
  `feedback` text DEFAULT NULL,
  `status` enum('Pending','Visited','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_history`
--

INSERT INTO `booking_history` (`id`, `user_id`, `token_id`, `department_id`, `hospital_id`, `booking_date`, `visited_date`, `doctor_name`, `diagnosis`, `treatment`, `follow_up_required`, `follow_up_date`, `notes`, `rating`, `feedback`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 12, '2025-12-14 07:37:18', '2025-12-16 07:37:18', 'Dr. Anil Kumar', 'Common Cold and Minor Fever', 'Paracetamol 500mg twice daily, Cough syrup, Rest and fluids', 1, '2025-12-21', 'Patient improved after 5 days. Follow-up done.', NULL, NULL, 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(2, 1, 2, 7, 16, '2025-12-29 07:37:18', '2025-12-30 07:37:18', 'Dr. Prakash Sharma', 'Hypertension Stage 1', 'BP checked: 142/90. Started on Lisinopril 5mg daily. Lifestyle modification advised.', 1, '2026-01-13', 'Patient compliant with medication. Next check-up for BP monitoring.', 5, 'Excellent service and professional doctor. Very satisfied.', 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(3, 1, 3, 1, 12, '2026-01-13 07:37:18', '2026-01-14 07:37:18', 'Dr. Ravi Nath', 'Flu-like illness with Respiratory discomfort', 'Oseltamivir prescribed, Cough suppressant, Complete rest recommended', 1, '2026-01-22', 'Patient had mild bronchitis. Chest clear after treatment. Advised to quit smoking.', 4, 'Good doctor, waited too long though.', 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(4, 1, 4, 6, 16, '2026-01-29 07:37:18', '2026-01-30 07:37:18', 'Dr. Bikram Poudel', 'Wrist Sprain (Right) - Mild', 'Ice compression bandage applied. Ibuprofen 400mg for pain. Immobilization for 1 week.', 1, '2026-02-16', 'Patient advised to avoid heavy lifting. Visit again after 1 week for reassessment.', 0, NULL, 'Visited', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(5, 1, 5, 2, 12, '2026-02-05 07:37:18', '2026-02-06 07:37:18', 'Dr. Sujan Raj', 'High Fever (103F) with Severe Headache', 'Blood tests done. Prescribed Paracetamol, Ibuprofen, Antibiotics pending test results', 1, '2026-02-14', 'Possible viral infection. Keep under observation. Call if temperature rises.', NULL, NULL, 'Visited', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(6, 1, 6, 1, 12, '2026-02-09 07:37:18', '2026-02-10 07:37:18', 'Dr. Anil Kumar', 'Routine Health Check-up', 'General examination done. Blood sugar level checked and normal. BP slightly elevated.', 1, '2026-03-14', 'Continue same medications. Reduce stress. Regular exercise recommended.', NULL, NULL, 'Pending', '2026-02-12 07:37:18', '2026-02-12 07:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `chronic_diseases`
--

CREATE TABLE `chronic_diseases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `disease_name` varchar(100) NOT NULL,
  `disease_code` varchar(20) DEFAULT NULL,
  `diagnosis_date` date DEFAULT NULL,
  `next_followup_date` date DEFAULT NULL,
  `last_visit_date` date DEFAULT NULL,
  `medications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`medications`)),
  `doctor_notes` text DEFAULT NULL,
  `status` enum('Active','Resolved','Suspended') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `chronic_diseases`
--

INSERT INTO `chronic_diseases` (`id`, `user_id`, `disease_name`, `disease_code`, `diagnosis_date`, `next_followup_date`, `last_visit_date`, `medications`, `doctor_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Diabetes Type 2', 'E11', '2025-02-12', '2026-03-14', '2026-02-05', '[{\"medicine\": \"Metformin\", \"dosage\": \"500mg\", \"frequency\": \"Twice daily\"}, {\"medicine\": \"Glipizide\", \"dosage\": \"5mg\", \"frequency\": \"Once daily\"}]', 'Patient doing well. Blood glucose levels within normal range. Continue current medication. Diet control advised.', 'Active', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(2, 1, 'Essential Hypertension', 'I10', '2025-08-16', '2026-03-05', '2026-01-29', '[{\"medicine\": \"Lisinopril\", \"dosage\": \"5mg\", \"frequency\": \"Once daily\"}, {\"medicine\": \"Amlodipine\", \"dosage\": \"5mg\", \"frequency\": \"Once daily\"}]', 'BP readings slightly elevated. Patient advised to reduce salt intake and exercise regularly. Medication adjusted slightly.', 'Active', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(3, 1, 'COPD - Mild', 'J44', '2025-10-15', '2026-03-29', '2026-01-22', '[{\"medicine\": \"Salbutamol\", \"dosage\": \"100mcg\", \"frequency\": \"As needed via inhaler\"}, {\"medicine\": \"Fluticasone\", \"dosage\": \"250mcg\", \"frequency\": \"Twice daily via inhaler\"}]', 'Mild COPD with good control. Patient given rescue inhaler. Advised to quit smoking immediately. Breathing exercises prescribed.', 'Active', '2026-02-12 07:37:18', '2026-02-12 07:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ne` varchar(100) NOT NULL,
  `description_en` text DEFAULT NULL,
  `description_ne` text DEFAULT NULL,
  `max_capacity` int(11) DEFAULT 50,
  `avg_service_time` int(11) DEFAULT 30,
  `current_load` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name_en`, `name_ne`, `description_en`, `description_ne`, `max_capacity`, `avg_service_time`, `current_load`, `is_active`, `created_at`) VALUES
(1, 'General Medicine', '??????? ????????', 'General health consultations and diagnoses', '??????? ????????? ???????', 50, 30, 0, 1, '2026-02-11 15:49:40'),
(2, 'Emergency', '???????', 'Emergency and critical care', '????????? ??????', 20, 15, 0, 1, '2026-02-11 15:49:40'),
(3, 'Maternal Health', '??????? ?????????', 'Pregnancy and maternal care', '?????????? ? ??????? ??????', 30, 25, 0, 1, '2026-02-11 15:49:40'),
(4, 'Chronic Disease', '?????? ???', 'Diabetes, hypertension, respiratory disease', '??????, ???? ???????', 40, 20, 0, 1, '2026-02-11 15:49:40'),
(5, 'Pediatrics', '???? ???', 'Child health and vaccinations', '?????? ? ???????', 35, 25, 0, 1, '2026-02-11 15:49:40'),
(6, 'Orthopedics', '????????', 'Bone and joint disorders', '????? ? ???? ?? ?????', 25, 30, 0, 1, '2026-02-11 15:49:40'),
(7, 'Cardiology', '???? ???', 'Heart and cardiovascular diseases', '????? ???', 20, 35, 0, 1, '2026-02-11 15:49:40'),
(8, 'ENT', '???, ???, ???', 'Ear, Nose, and Throat', '??? ??? ???', 30, 20, 0, 1, '2026-02-11 15:49:40'),
(9, 'General Medicine', '????????????????????? ????????????????????????', 'General health consultations and diagnoses', '????????????????????? ??????????????????????????? ?????????????????????', 50, 30, 0, 1, '2026-02-12 04:17:59'),
(10, 'Emergency', '?????????????????????', 'Emergency and critical care', '??????????????????????????? ??????????????????', 20, 15, 0, 1, '2026-02-12 04:17:59'),
(11, 'Maternal Health', '????????????????????? ???????????????????????????', 'Pregnancy and maternal care', '?????????????????????????????? ??? ????????????????????? ??????????????????', 30, 25, 0, 1, '2026-02-12 04:17:59'),
(12, 'Chronic Disease', '?????????????????? ?????????', 'Diabetes, hypertension, respiratory disease', '??????????????????, ???????????? ?????????????????????', 40, 20, 0, 1, '2026-02-12 04:17:59'),
(13, 'Pediatrics', '???????????? ?????????', 'Child health and vaccinations', '?????????????????? ??? ?????????????????????', 35, 25, 0, 1, '2026-02-12 04:17:59'),
(14, 'Orthopedics', '????????????????????????', 'Bone and joint disorders', '??????????????? ??? ???????????? ?????? ???????????????', 25, 30, 0, 1, '2026-02-12 04:17:59'),
(15, 'Cardiology', '???????????? ?????????', 'Heart and cardiovascular diseases', '??????????????? ?????????', 20, 35, 0, 1, '2026-02-12 04:17:59'),
(16, 'ENT', '?????????, ?????????, ?????????', 'Ear, Nose, and Throat', '????????? ????????? ?????????', 30, 20, 0, 1, '2026-02-12 04:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `health_assessments`
--

CREATE TABLE `health_assessments` (
  `id` int(11) NOT NULL,
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
  `chronic_disease_types` longtext DEFAULT NULL COMMENT 'JSON array of chronic diseases',
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

--
-- Dumping data for table `health_assessments`
--

INSERT INTO `health_assessments` (`id`, `user_id`, `token_id`, `assessment_date`, `has_fever`, `fever_days`, `difficulty_breathing`, `has_injury`, `injury_severity`, `is_pregnant`, `has_chronic_disease`, `chronic_disease_types`, `has_emergency_signs`, `additional_notes`, `assessment_district`, `assessment_municipality`, `assessment_ward`, `department_id`, `hospital_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, '2026-02-12 07:29:27', 1, 3, 1, 1, NULL, 1, 1, '[\"Diabetes\"]', 1, 'Test assessment created for verification', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 1, 1, 'Active', '2026-02-12 07:29:27', '2026-02-12 07:29:27'),
(2, 1, 1, '2025-12-14 07:37:18', 1, 4, 0, 0, NULL, 0, 0, NULL, 0, 'Common cold with mild fever. Prescribed antibiotics and rest', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 1, 12, 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(3, 1, NULL, '2025-12-29 07:37:18', 0, 0, 0, 0, NULL, 0, 1, '[\"Diabetes\", \"Type 2\"]', 0, 'Regular diabetes follow-up. Blood glucose levels normal. Continue current medication.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 4, 12, 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(4, 1, 2, '2026-01-13 07:37:18', 0, 0, 0, 0, NULL, 0, 1, '[\"Hypertension\"]', 0, 'Blood pressure elevated. Recommended lifestyle changes and medication adjustment.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 7, 16, 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(5, 1, 3, '2026-01-22 07:37:18', 1, 5, 1, 0, NULL, 0, 1, '[\"COPD\"]', 0, 'Mild respiratory distress. Chest X-ray normal. Prescribed inhalers and breathing exercises.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 1, 12, 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(6, 1, 4, '2026-01-29 07:37:18', 0, 0, 0, 1, 'Moderate', 0, 0, NULL, 0, 'Sprained wrist from fall. Bandaged and advised rest for 2 weeks. Follow-up after 5 days.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 6, 16, 'Completed', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(7, 1, 5, '2026-02-05 07:37:18', 1, 3, 0, 0, NULL, 0, 0, NULL, 0, 'Severe migraine with high fever. Blood work normal. Prescribed painkillers and fluids.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 1, 12, 'Active', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(8, 1, 6, '2026-02-09 07:37:18', 0, 0, 0, 0, NULL, 0, 1, '[\"Diabetes\", \"Hypertension\"]', 0, 'General health check. All parameters normal. Continue with current medication. Next visit in 1 month.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 1, 12, 'Active', '2026-02-12 07:37:18', '2026-02-12 07:37:18'),
(9, 1, 7, '2026-02-11 07:37:18', 1, 2, 1, 0, NULL, 0, 1, '[\"Diabetes\"]', 1, 'Emergency visit due to extreme fever and breathing difficulty. Possible pneumonia. Hospitalization recommended. Awaiting X-ray report.', 'Kathmandu', 'Kathmandu Metropolitan City', '5', 2, 12, 'Active', '2026-02-12 07:37:18', '2026-02-12 07:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `health_records`
--

CREATE TABLE `health_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `visit_date` date DEFAULT NULL,
  `symptoms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`symptoms`)),
  `diagnosis` text DEFAULT NULL,
  `treatment_plan` text DEFAULT NULL,
  `doctor_name` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `health_records`
--

INSERT INTO `health_records` (`id`, `user_id`, `token_id`, `visit_date`, `symptoms`, `diagnosis`, `treatment_plan`, `doctor_name`, `department_id`, `notes`, `created_at`) VALUES
(1, 1, 1, '2025-12-16', '[\"Fever\", \"Cough\", \"Runny nose\"]', 'Common Cold - Viral Infection', 'Rest, fluids, Paracetamol 500mg twice daily, Cough syrup', 'Dr. Anil Kumar', 1, 'Patient recovering well. Advised to return if symptoms persist.', '2026-02-12 07:37:18'),
(2, 1, 2, '2025-12-30', '[\"High Blood Pressure\", \"Mild Headache\"]', 'Essential Hypertension Stage 1', 'Lisinopril 5mg once daily, Salt reduction, Regular exercise', 'Dr. Prakash Sharma', 7, 'BP: 142/90 mmHg. Patient compliant. Monitor BP regularly.', '2026-02-12 07:37:18'),
(3, 1, 3, '2026-01-14', '[\"Fever\", \"Cough\", \"Difficulty Breathing\"]', 'Bronchitis (Viral)', 'Oseltamivir, Bromhexine, Complete bed rest for 5 days', 'Dr. Ravi Nath', 1, 'Chest sounds clear. No pneumonia detected. Patient to avoid smoking.', '2026-02-12 07:37:18'),
(4, 1, 4, '2026-01-30', '[\"Right wrist pain\", \"Swelling\"]', 'Grade 1 Wrist Sprain', 'Ice compresses, Compression bandage, Ibuprofen 400mg 3x daily for 1 week', 'Dr. Bikram Poudel', 6, 'Immobilize wrist. Avoid heavy lifting. Re-examine after 1 week.', '2026-02-12 07:37:18'),
(5, 1, 5, '2026-02-06', '[\"Very high fever (103F)\", \"Severe headache\", \"Body aches\"]', 'Acute Viral Illness (Possibly Flu)', 'Antipyretics, Blood tests pending, IV fluids if needed', 'Dr. Sujan Raj', 2, 'Monitor closely. Fever spike at night. Possible secondary infection risk.', '2026-02-12 07:37:18'),
(6, 1, 6, '2026-02-10', '[\"General fatigue\", \"Blood sugar check\", \"BP elevated\"]', 'Routine Check-up - Hypertension and Diabetes Management', 'Continue Metformin 500mg 2x daily, Continue Lisinopril 5mg daily, Diet modification', 'Dr. Anil Kumar', 1, 'All vitals within normal range except BP. Patient to return in 1 month.', '2026-02-12 07:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `hospital_locations`
--

CREATE TABLE `hospital_locations` (
  `id` int(11) NOT NULL,
  `hospital_name` varchar(150) NOT NULL,
  `district` varchar(100) NOT NULL,
  `municipality` varchar(100) NOT NULL,
  `ward` varchar(50) DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `address` text DEFAULT NULL,
  `specialities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of specialities',
  `phone` varchar(20) DEFAULT NULL,
  `type` enum('Government','Private','Specialized','Non-Profit') DEFAULT 'Private',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_locations`
--

INSERT INTO `hospital_locations` (`id`, `hospital_name`, `district`, `municipality`, `ward`, `latitude`, `longitude`, `address`, `specialities`, `phone`, `type`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bhaktapur Hospital', 'Bhaktapur', 'Bhaktapur Municipality', 'Central', 27.67190000, 85.42200000, 'Bhaktapur 44800, Nepal', '[\"General Surgery\", \"Emergency\", \"Internal Medicine\"]', '+977-1-6610798', 'Government', 'Public district hospital providing multi-specialty basic services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(2, 'Bhaktapur Cancer Hospital', 'Bhaktapur', 'Bhaktapur Municipality', 'Dudh Pati', 27.67310000, 85.42230000, 'MCFC+7W8, Bhaktapur 44800, Nepal', '[\"Oncology\", \"Cancer Care\"]', '+977-1-6611532', 'Specialized', 'Specialized oncology care and wards including ICU and surgical oncology', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(3, 'Bhaktapur International Hospital', 'Bhaktapur', 'Madhyapur Thimi Municipality', 'General', 27.66200000, 85.35100000, 'Madhyapur Thimi 44600, Bhaktapur', '[\"General Medicine\", \"Emergency\", \"Surgery\"]', '+977-1-6618765', 'Private', 'Full-service private hospital serving general treatments', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(4, 'Bhaktapur Model Hospital', 'Bhaktapur', 'Madhyapur Thimi Municipality', 'General', 27.66400000, 85.35200000, 'Araniko Highway, Madhyapur Thimi 44800', '[\"General Healthcare\", \"Emergency\", \"Pediatrics\"]', '+977-1-6615432', 'Private', 'General healthcare & emergency services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(5, 'Khwopa Hospital', 'Bhaktapur', 'Bhaktapur Municipality', '9', 27.67500000, 85.43000000, 'Ward-9, Garud Kundal Road, Bhaktapur 44800', '[\"General Medicine\", \"Emergency\", \"Surgery\"]', '+977-1-6612000', 'Private', 'General hospital with comprehensive services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(6, 'KMC Hospital - Duwakot', 'Bhaktapur', 'Changunarayan Municipality', 'General', 27.68000000, 85.41000000, 'Duwakot Rd, Bhaktapur 44800', '[\"General Medicine\", \"Emergency\"]', '+977-1-6610500', 'Private', 'General hospital with modern facilities', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(7, 'Suryabinayak Municipal Hospital', 'Bhaktapur', 'Suryabinayak Municipality', 'General', 27.69000000, 85.44000000, 'Araniko Highway, Bhaktapur 44800', '[\"Community Health\", \"General Medicine\"]', '+977-1-6614500', 'Government', 'Community general health services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(8, 'Khwopa Tilganga Eye Hospital', 'Bhaktapur', 'Bhaktapur Municipality', 'General', 27.67200000, 85.42250000, 'Bhaktapur 44800', '[\"Ophthalmology\", \"Eye Care\"]', '+977-1-6611000', 'Specialized', 'Eye care and ophthalmology services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(9, 'Shahid Dharmabhakta National Transplant Center', 'Bhaktapur', 'Bhaktapur Municipality', 'General', 27.67250000, 85.42300000, 'Bhaktapur 44800', '[\"Transplant Surgery\", \"Specialized Care\"]', '+977-1-6615000', 'Government', 'Transplant surgery & specialized care', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(10, 'Dr. Iwamura Memorial Hospital', 'Bhaktapur', 'Bhaktapur Municipality', 'General', 27.67100000, 85.42400000, 'Nagarkot Road, Bhaktapur 44800', '[\"General Medicine\", \"Emergency\"]', '+977-1-6615678', 'Private', 'Private general hospital', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(11, 'Nagrik Hospital', 'Bhaktapur', 'Madhyapur Thimi Municipality', 'General', 27.66300000, 85.35150000, 'Araniko Highway, Madhyapur Thimi 44800', '[\"General Care\", \"Emergency\"]', '+977-1-6612345', 'Private', 'General care services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(12, 'Bir Hospital', 'Kathmandu', 'Kathmandu Metropolitan City', 'Central', 27.71720000, 85.32400000, 'Kathmandu 44600, Nepal', '[\"General Medicine\", \"Emergency\", \"Surgery\", \"Cardiology\", \"Pediatrics\"]', '+977-1-4224881', 'Government', 'Government general hospital with emergency & multi-specialty services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(13, 'T.U. Teaching Hospital', 'Kathmandu', 'Kathmandu Metropolitan City', 'Maharajgunj', 27.72700000, 85.31800000, 'Maharajgunj Sadak, Kathmandu 44600', '[\"General Medicine\", \"Surgery\", \"Pediatrics\", \"Internal Medicine\"]', '+977-1-4412801', 'Government', 'Teaching hospital with comprehensive general medicine services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(14, 'Kathmandu Model Hospital', 'Kathmandu', 'Kathmandu Metropolitan City', 'Baneshwar', 27.71400000, 85.33500000, 'Red Cross Marg, Kathmandu 44600', '[\"General Care\", \"Emergency\", \"Surgery\"]', '+977-1-4228228', 'Private', 'General care and emergency services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(15, 'Nepal Police Hospital', 'Kathmandu', 'Kathmandu Metropolitan City', 'Chhauni', 27.70800000, 85.31200000, 'Krishna Dhara Marg, Kathmandu 44600', '[\"General Medicine\", \"Emergency\"]', '+977-1-4215050', 'Government', 'Government hospital services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(16, 'Kantipur Hospital', 'Kathmandu', 'Kathmandu Metropolitan City', 'Silchok', 27.72000000, 85.34000000, 'Shri Ganesh Marg, Kathmandu 44600', '[\"Multi-specialty\", \"Surgery\", \"Cardiology\", \"Orthopedics\"]', '+977-1-4261451', 'Private', 'Multi-specialty hospital', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(17, 'Civil Service Hospital', 'Kathmandu', 'Kathmandu Metropolitan City', 'Minbhawan', 27.72200000, 85.33000000, 'Minbhawan Marg, Kathmandu 44600', '[\"General Medicine\", \"Emergency\"]', '+977-1-4262058', 'Government', 'Government general health services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(18, 'Scheer Memorial Adventist Hospital', 'Kavre', 'Banepa Municipality', 'General', 27.65500000, 85.53800000, 'JGMG+9W8, Banepa 45210, Nepal', '[\"General Medicine\", \"Surgery\", \"Pediatrics\"]', '+977-1-6620099', 'Private', 'General hospital with comprehensive services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(19, 'Satya Sai Hospital', 'Kavre', 'Banepa Municipality', 'General', 27.65700000, 85.54000000, 'Araniko Highway, Banepa 45210', '[\"Multi-specialty\", \"General Medicine\", \"Surgery\"]', '+977-1-6620456', 'Private', 'Multi-specialty hospital services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(20, 'HRDC - Hospital & Rehabilitation Center', 'Kavre', 'Banepa Municipality', '11', 27.65800000, 85.54200000, 'Ugratara Janagal, Banepa-11', '[\"Pediatrics\", \"Rehabilitation\", \"Child Care\"]', '+977-1-6620789', 'Specialized', 'Rehabilitation & pediatric focus hospital', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(21, 'K.B. Hospital Pvt. Ltd', 'Kavre', 'Banepa Municipality', 'General', 27.65900000, 85.54400000, 'Prabesh Marga, Banepa 45210', '[\"General Care\", \"Emergency\"]', '+977-1-6621000', 'Private', 'General care services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(22, 'Reiyukai Eiko Masunaga Eye Hospital', 'Kavre', 'Banepa Municipality', 'General', 27.66000000, 85.54600000, 'Banepa 45210', '[\"Ophthalmology\", \"Eye Care\"]', '+977-1-6621234', 'Specialized', 'Eye care and ophthalmology services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(23, 'Jujubhai Memorial Health Service', 'Kavre', 'Banepa Municipality', 'General', 27.66100000, 85.54800000, 'Banepa 45210', '[\"General Health\", \"Community Care\"]', '+977-1-6621567', 'Non-Profit', 'General health services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11'),
(24, 'Big Care Hospital', 'Kavre', 'Banepa Municipality', '10', 27.66200000, 85.55000000, 'Janagal, Banepa-10', '[\"General Care\", \"Emergency\"]', '+977-1-6621890', 'Private', 'General care services', 1, '2026-02-12 06:39:11', '2026-02-12 06:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `maternal_health`
--

CREATE TABLE `maternal_health` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pregnancy_status` enum('Pregnant','Post-Partum','Not Pregnant') DEFAULT 'Pregnant',
  `expected_due_date` date DEFAULT NULL,
  `last_menstrual_period` date DEFAULT NULL,
  `antenatal_visits_completed` int(11) DEFAULT 0,
  `next_antenatal_date` date DEFAULT NULL,
  `vaccinations_needed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vaccinations_needed`)),
  `last_checkup_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_high_risk` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `nearby_hospitals_view`
-- (See below for the actual view)
--
CREATE TABLE `nearby_hospitals_view` (
`id` int(11)
,`hospital_name` varchar(150)
,`district` varchar(100)
,`municipality` varchar(100)
,`ward` varchar(50)
,`latitude` decimal(10,8)
,`longitude` decimal(11,8)
,`specialities` longtext
,`phone` varchar(20)
,`type` enum('Government','Private','Specialized','Non-Profit')
,`description` text
);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('Token','Chronic','Maternal','Appointment','System') DEFAULT 'System',
  `message_en` text NOT NULL,
  `message_ne` text NOT NULL,
  `is_sms` tinyint(1) DEFAULT 0,
  `is_sent` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivery_status` enum('Pending','Sent','Failed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offline_bookings`
--

CREATE TABLE `offline_bookings` (
  `id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `otp_sessions`
--

CREATE TABLE `otp_sessions` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `mpin` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `attempt_count` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `booking_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('Pending','Verified','Expired','Used') DEFAULT 'Pending',
  `sms_sent` tinyint(1) DEFAULT 0 COMMENT 'Whether SMS was actually sent (1) or only generated (0)',
  `sms_sent_at` timestamp NULL DEFAULT NULL COMMENT 'When SMS was sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otp_sessions`
--

INSERT INTO `otp_sessions` (`id`, `phone_number`, `otp_code`, `mpin`, `is_verified`, `attempt_count`, `max_attempts`, `created_at`, `expires_at`, `verified_at`, `booking_data`, `status`, `sms_sent`, `sms_sent_at`) VALUES
(27, '9844634579', '550184', '1661', 1, 0, 3, '2026-02-12 06:41:28', NULL, '2026-02-12 06:41:56', NULL, 'Used', 1, '2026-02-12 06:41:28'),
(28, '9844634579', '809051', '3403', 1, 0, 3, '2026-02-12 06:52:48', NULL, '2026-02-12 06:53:00', NULL, 'Used', 1, '2026-02-12 06:52:48'),
(29, '9803962360', '121932', '6508', 1, 0, 3, '2026-02-12 06:57:57', NULL, '2026-02-12 07:11:26', NULL, 'Used', 1, '2026-02-12 07:11:17'),
(30, '9844634579', '748780', '6335', 1, 1, 3, '2026-02-12 07:04:28', NULL, '2026-02-12 07:04:55', NULL, 'Verified', 1, '2026-02-12 07:04:29');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_department_id` int(11) DEFAULT NULL,
  `to_department_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `referred_date` date DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_ne` varchar(100) NOT NULL,
  `type` enum('Emergency','Referral','Education','Regular') DEFAULT 'Regular',
  `description_en` text DEFAULT NULL,
  `description_ne` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name_en`, `name_ne`, `type`, `description_en`, `description_ne`, `is_active`, `created_at`) VALUES
(1, 'Emergency Triage', '??????? ????', 'Emergency', 'Immediate assessment for emergencies', '?????? ????????? ?????????', 1, '2026-02-11 15:49:40'),
(2, 'Chronic Disease Follow-up', '??????????? ??? ????????', 'Regular', 'Regular check-ups for chronic diseases', '?????? ????????? ?? ????', 1, '2026-02-11 15:49:40'),
(3, 'Maternal Check-up', '??????? ????', 'Regular', 'Pregnancy and postnatal care', '?????????? ?? ?????????? ??????', 1, '2026-02-11 15:49:40'),
(4, 'Vaccination', '???????', 'Regular', 'Immunization services', '?????????? ??????', 1, '2026-02-11 15:49:40'),
(5, 'Referral Service', '????? ????', 'Referral', 'Referral to specialized departments', '????? ??????? ??? ?????', 1, '2026-02-11 15:49:40'),
(6, 'Health Education', '????????? ??????', 'Education', 'Health awareness and prevention tips', '????????? ???????? ?? ??????', 1, '2026-02-11 15:49:40'),
(7, 'Emergency Triage', '????????????????????? ????????????', 'Emergency', 'Immediate assessment for emergencies', '?????????????????? ??????????????????????????? ???????????????????????????', 1, '2026-02-12 04:17:59'),
(8, 'Chronic Disease Follow-up', '????????????????????????????????? ????????? ????????????????????????', 'Regular', 'Regular check-ups for chronic diseases', '?????????????????? ??????????????????????????? ?????? ????????????', 1, '2026-02-12 04:17:59'),
(9, 'Maternal Check-up', '????????????????????? ????????????', 'Regular', 'Pregnancy and postnatal care', '?????????????????????????????? ?????? ?????????????????????????????? ??????????????????', 1, '2026-02-12 04:17:59'),
(10, 'Vaccination', '?????????????????????', 'Regular', 'Immunization services', '?????????????????????????????? ??????????????????', 1, '2026-02-12 04:17:59'),
(11, 'Referral Service', '??????????????? ????????????', 'Referral', 'Referral to specialized departments', '??????????????? ????????????????????? ????????? ???????????????', 1, '2026-02-12 04:17:59'),
(12, 'Health Education', '??????????????????????????? ??????????????????', 'Education', 'Health awareness and prevention tips', '??????????????????????????? ???????????????????????? ?????? ??????????????????', 1, '2026-02-12 04:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `symptom_hospital_mapping`
--

CREATE TABLE `symptom_hospital_mapping` (
  `id` int(11) NOT NULL,
  `symptom_name` varchar(100) NOT NULL,
  `required_specialities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of required specialities',
  `priority_level` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `symptom_hospital_mapping`
--

INSERT INTO `symptom_hospital_mapping` (`id`, `symptom_name`, `required_specialities`, `priority_level`, `created_at`) VALUES
(1, 'Fever', '[\"General Medicine\", \"Emergency\", \"Internal Medicine\"]', 'Normal', '2026-02-12 06:38:58'),
(2, 'Difficulty Breathing', '[\"Emergency\", \"General Medicine\", \"Internal Medicine\"]', 'Emergency', '2026-02-12 06:38:58'),
(3, 'Chest Pain', '[\"Cardiology\", \"Emergency\", \"Surgery\"]', 'Emergency', '2026-02-12 06:38:58'),
(4, 'Injury', '[\"Surgery\", \"Emergency\", \"Orthopedics\"]', 'Priority', '2026-02-12 06:38:58'),
(5, 'Pregnancy Related', '[\"Maternal Health\", \"General Medicine\"]', 'Priority', '2026-02-12 06:38:58'),
(6, 'Diabetes', '[\"General Medicine\", \"Internal Medicine\"]', 'Chronic', '2026-02-12 06:38:58'),
(7, 'Hypertension', '[\"Cardiology\", \"General Medicine\", \"Internal Medicine\"]', 'Chronic', '2026-02-12 06:38:58'),
(8, 'Eye Problem', '[\"Ophthalmology\", \"General Medicine\"]', 'Normal', '2026-02-12 06:38:58'),
(9, 'Child Health', '[\"Pediatrics\", \"General Medicine\"]', 'Normal', '2026-02-12 06:38:58'),
(10, 'Bone/Joint Problem', '[\"Orthopedics\", \"Surgery\", \"Emergency\"]', 'Priority', '2026-02-12 06:38:58');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','password','json') DEFAULT 'text',
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `category`, `created_at`, `updated_at`) VALUES
(1, 'sms_enabled', '1', 'boolean', 'sms', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(2, 'sms_api_url', 'http://api.sparrowsms.com/v2/', 'text', 'sms', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(3, 'sms_api_key', 'v2_OWEzBIx5dP19w0GD8kbAoYtjNrN.RrsP', 'password', 'sms', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(4, 'sms_sender_id', 'TheAlert', 'text', 'sms', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(5, 'otp_expiry_minutes', '10', 'number', 'security', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(6, 'max_otp_attempts', '3', 'number', 'security', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(7, 'max_otp_send_attempts', '5', 'number', 'security', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(8, 'otp_resend_cooldown', '60', 'number', 'security', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(9, 'mpin_auto_generate', '1', 'boolean', 'security', '2026-02-12 04:28:01', '2026-02-12 04:28:01'),
(10, 'mpin_send_via_sms', '1', 'boolean', 'sms', '2026-02-12 04:28:01', '2026-02-12 04:28:01');

-- --------------------------------------------------------

--
-- Table structure for table `tokens`
--

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `user_district` varchar(100) DEFAULT NULL,
  `user_municipality` varchar(100) DEFAULT NULL,
  `user_ward` varchar(50) DEFAULT NULL,
  `token_number` int(11) NOT NULL,
  `priority` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `triage_reason` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`triage_reason`)),
  `status` enum('Active','Called','Completed','Missed','Rescheduled') DEFAULT 'Active',
  `estimated_wait_time` int(11) DEFAULT NULL,
  `is_emergency` tinyint(1) DEFAULT 0,
  `is_chronic_followup` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `called_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tokens`
--

INSERT INTO `tokens` (`id`, `user_id`, `department_id`, `hospital_id`, `user_district`, `user_municipality`, `user_ward`, `token_number`, `priority`, `triage_reason`, `status`, `estimated_wait_time`, `is_emergency`, `is_chronic_followup`, `created_at`, `called_at`, `completed_at`) VALUES
(1, 1, 1, NULL, NULL, NULL, NULL, 1, 'Normal', '{\"full_name\":\"Test User\",\"have_fever\":false,\"fever_days\":0,\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":false,\"additional_notes\":\"Test booking\"}', 'Active', 0, 0, 0, '2026-02-12 05:19:24', NULL, NULL),
(2, 1, 1, NULL, NULL, NULL, NULL, 2, 'Normal', '{\"full_name\":\"Test User\",\"have_fever\":false,\"fever_days\":0,\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":false,\"additional_notes\":\"Test booking\"}', 'Active', 30, 0, 0, '2026-02-12 05:20:29', NULL, NULL),
(3, 1, 7, NULL, NULL, NULL, NULL, 1, 'Emergency', '{\"full_name\":\"youvraj syangtan\",\"have_fever\":false,\"fever_days\":\"\",\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":true,\"additional_notes\":\"segserg\"}', 'Active', 0, 1, 0, '2026-02-12 05:31:09', NULL, NULL),
(4, 1, 7, NULL, NULL, NULL, NULL, 2, 'Emergency', '{\"full_name\":\"youvraj syangtan\",\"have_fever\":true,\"fever_days\":\"\",\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":true,\"additional_notes\":\"regsdgds\"}', 'Active', 35, 1, 0, '2026-02-12 05:46:53', NULL, NULL),
(5, 1, 7, NULL, NULL, NULL, NULL, 3, 'Emergency', '{\"full_name\":\"youvraj syangtan\",\"have_fever\":false,\"fever_days\":\"\",\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":true,\"additional_notes\":\"werqwr\"}', 'Active', 70, 1, 0, '2026-02-12 05:53:53', NULL, NULL),
(6, 2, 7, NULL, 'Kathmandu', '', '', 4, 'Emergency', '{\"full_name\":\"youvraj syangtan\",\"have_fever\":false,\"fever_days\":\"\",\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":true,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":true,\"additional_notes\":\"vbcb\"}', 'Active', 105, 1, 0, '2026-02-12 06:44:53', NULL, NULL),
(7, 2, 7, NULL, 'Bhaktapur', 'Bhaktapur Municipality', '6', 5, 'Priority', '{\"full_name\":\"uouoououlllldfgh\",\"have_fever\":true,\"fever_days\":\"12\",\"difficulty_breathing\":false,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":false,\"additional_notes\":\"dzsgsdg\"}', 'Active', 140, 0, 0, '2026-02-12 06:53:32', NULL, NULL),
(8, 1, 7, NULL, 'Bhaktapur', 'Madhyapur Thimi Municipality', '11', 6, 'Priority', '{\"full_name\":\"youvraj syangtan\",\"have_fever\":true,\"fever_days\":\"\",\"difficulty_breathing\":true,\"any_injury\":false,\"injury_severity\":\"\",\"are_pregnant\":false,\"chronic_disease\":false,\"chronic_disease_names\":[],\"emergency_signs\":false,\"additional_notes\":\"cbxb\"}', 'Active', 175, 0, 0, '2026-02-12 07:11:54', NULL, NULL),
(9, 1, 7, NULL, 'Kathmandu', 'Kathmandu Metropolitan City', '5', 101, 'Normal', '{\"full_name\":\"Test User\",\"have_fever\":true,\"fever_days\":\"4\",\"difficulty_breathing\":false,\"any_injury\":false,\"chronic_disease\":false,\"emergency_signs\":false,\"additional_notes\":\"Common cold\"}', 'Completed', 15, 0, 0, '2025-12-14 07:37:18', NULL, '2025-12-16 07:37:18'),
(10, 1, 4, NULL, 'Kathmandu', 'Kathmandu Metropolitan City', '5', 102, 'Chronic', '{\"full_name\":\"Test User\",\"have_fever\":false,\"fever_days\":\"0\",\"difficulty_breathing\":false,\"any_injury\":false,\"chronic_disease\":true,\"chronic_disease_names\":[\"Diabetes\"],\"emergency_signs\":false,\"additional_notes\":\"Monthly diabetes check-up\"}', 'Completed', 20, 0, 1, '2025-12-29 07:37:18', NULL, '2025-12-30 07:37:18'),
(11, 1, 1, NULL, 'Kathmandu', 'Kathmandu Metropolitan City', '5', 103, 'Priority', '{\"full_name\":\"Test User\",\"have_fever\":true,\"fever_days\":\"5\",\"difficulty_breathing\":true,\"any_injury\":false,\"chronic_disease\":false,\"emergency_signs\":false,\"additional_notes\":\"Respiratory issue and fever\"}', 'Completed', 10, 0, 0, '2026-01-13 07:37:18', NULL, '2026-01-14 07:37:18'),
(12, 1, 6, NULL, 'Kathmandu', 'Kathmandu Metropolitan City', '5', 104, 'Priority', '{\"full_name\":\"Test User\",\"have_fever\":false,\"fever_days\":\"0\",\"difficulty_breathing\":false,\"any_injury\":true,\"injury_severity\":\"Moderate\",\"chronic_disease\":false,\"emergency_signs\":false,\"additional_notes\":\"Wrist sprain from accident\"}', 'Completed', 5, 0, 0, '2026-01-29 07:37:18', NULL, '2026-01-30 07:37:18'),
(13, 1, 2, NULL, 'Kathmandu', 'Kathmandu Metropolitan City', '5', 105, 'Emergency', '{\"full_name\":\"Test User\",\"have_fever\":true,\"fever_days\":\"3\",\"difficulty_breathing\":false,\"any_injury\":false,\"chronic_disease\":false,\"emergency_signs\":false,\"additional_notes\":\"High fever and severe headache\"}', 'Completed', 2, 1, 0, '2026-02-05 07:37:18', NULL, '2026-02-06 07:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `triage_responses`
--

CREATE TABLE `triage_responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_id` int(11) DEFAULT NULL,
  `has_fever` tinyint(1) DEFAULT 0,
  `fever_duration` int(11) DEFAULT NULL,
  `difficulty_breathing` tinyint(1) DEFAULT 0,
  `injury` tinyint(1) DEFAULT 0,
  `injury_severity` varchar(50) DEFAULT NULL,
  `pregnancy` tinyint(1) DEFAULT 0,
  `chronic_disease` tinyint(1) DEFAULT 0,
  `chronic_disease_names` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`chronic_disease_names`)),
  `emergency_signs` tinyint(1) DEFAULT 0,
  `additional_notes` text DEFAULT NULL,
  `assigned_priority` enum('Emergency','Priority','Normal','Chronic') DEFAULT 'Normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
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
  `is_pregnant` tinyint(1) DEFAULT 0,
  `chronic_diseases` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`chronic_diseases`)),
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone_number`, `full_name`, `age`, `gender`, `email`, `district`, `municipality`, `ward`, `latitude`, `longitude`, `mpin`, `old_mpin`, `is_pregnant`, `chronic_diseases`, `created_at`, `updated_at`, `phone_verified`, `avatar_color`, `last_booking_date`, `total_bookings`, `blood_type`, `allergies`, `emergency_contact`, `emergency_contact_name`) VALUES
(1, '9803962360', 'Test User Complete', 35, 'Male', 'testuser@smarthealth.local', 'Kathmandu', 'Kathmandu Metropolitan City', '5', NULL, NULL, '3736', '5479', 0, NULL, '2026-02-12 05:19:24', '2026-02-12 07:37:18', 1, 'info', '2026-02-12 07:37:18', 6, 'O+', 'Penicillin, Shellfish', '9841234567', 'Ramesh Kumar (Father)'),
(2, '9844634579', 'youvraj syangtan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '9599', '9868', 0, NULL, '2026-02-12 06:44:53', '2026-02-12 06:53:32', 0, 'primary', NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_locations`
--

CREATE TABLE `user_locations` (
  `id` int(11) NOT NULL,
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

-- --------------------------------------------------------

--
-- Structure for view `nearby_hospitals_view`
--
DROP TABLE IF EXISTS `nearby_hospitals_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `nearby_hospitals_view`  AS SELECT `h`.`id` AS `id`, `h`.`hospital_name` AS `hospital_name`, `h`.`district` AS `district`, `h`.`municipality` AS `municipality`, `h`.`ward` AS `ward`, `h`.`latitude` AS `latitude`, `h`.`longitude` AS `longitude`, `h`.`specialities` AS `specialities`, `h`.`phone` AS `phone`, `h`.`type` AS `type`, `h`.`description` AS `description` FROM `hospital_locations` AS `h` WHERE `h`.`is_active` = 1 ORDER BY `h`.`district` ASC, `h`.`municipality` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_id` (`token_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_booking_date` (`booking_date`);

--
-- Indexes for table `chronic_diseases`
--
ALTER TABLE `chronic_diseases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_followup` (`next_followup_date`),
  ADD KEY `idx_chronic_date` (`next_followup_date`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_assessments`
--
ALTER TABLE `health_assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token_id` (`token_id`),
  ADD KEY `idx_assessment_date` (`assessment_date`);

--
-- Indexes for table `health_records`
--
ALTER TABLE `health_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `token_id` (`token_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_visit_date` (`visit_date`);

--
-- Indexes for table `hospital_locations`
--
ALTER TABLE `hospital_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hospital` (`hospital_name`,`district`),
  ADD KEY `idx_district` (`district`),
  ADD KEY `idx_municipality` (`municipality`),
  ADD KEY `idx_ward` (`ward`);

--
-- Indexes for table `maternal_health`
--
ALTER TABLE `maternal_health`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_due_date` (`expected_due_date`),
  ADD KEY `idx_maternal_date` (`next_antenatal_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`delivery_status`),
  ADD KEY `idx_notification_date` (`created_at`);

--
-- Indexes for table `offline_bookings`
--
ALTER TABLE `offline_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booked_by_staff_id` (`booked_by_staff_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `token_id` (`token_id`);

--
-- Indexes for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `from_department_id` (`from_department_id`),
  ADD KEY `to_department_id` (`to_department_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `symptom_hospital_mapping`
--
ALTER TABLE `symptom_hospital_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_symptom` (`symptom_name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_token_date` (`created_at`),
  ADD KEY `idx_hospital_id` (`hospital_id`),
  ADD KEY `idx_user_location` (`user_district`,`user_municipality`);

--
-- Indexes for table `triage_responses`
--
ALTER TABLE `triage_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token_id` (`token_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_user_location` (`district`,`municipality`);

--
-- Indexes for table `user_locations`
--
ALTER TABLE `user_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_district_municipality` (`district`,`municipality`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `booking_history`
--
ALTER TABLE `booking_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chronic_diseases`
--
ALTER TABLE `chronic_diseases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `health_assessments`
--
ALTER TABLE `health_assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `health_records`
--
ALTER TABLE `health_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `hospital_locations`
--
ALTER TABLE `hospital_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `maternal_health`
--
ALTER TABLE `maternal_health`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offline_bookings`
--
ALTER TABLE `offline_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `symptom_hospital_mapping`
--
ALTER TABLE `symptom_hospital_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tokens`
--
ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `triage_responses`
--
ALTER TABLE `triage_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_locations`
--
ALTER TABLE `user_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD CONSTRAINT `booking_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_history_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_history_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `chronic_diseases`
--
ALTER TABLE `chronic_diseases`
  ADD CONSTRAINT `chronic_diseases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_assessments`
--
ALTER TABLE `health_assessments`
  ADD CONSTRAINT `health_assessments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_assessments_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_assessments_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `health_records`
--
ALTER TABLE `health_records`
  ADD CONSTRAINT `health_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `health_records_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `health_records_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `maternal_health`
--
ALTER TABLE `maternal_health`
  ADD CONSTRAINT `maternal_health_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offline_bookings`
--
ALTER TABLE `offline_bookings`
  ADD CONSTRAINT `offline_bookings_ibfk_1` FOREIGN KEY (`booked_by_staff_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `offline_bookings_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offline_bookings_ibfk_3` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`from_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `fk_hospital_id` FOREIGN KEY (`hospital_id`) REFERENCES `hospital_locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tokens_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `triage_responses`
--
ALTER TABLE `triage_responses`
  ADD CONSTRAINT `triage_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `triage_responses_ibfk_2` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_locations`
--
ALTER TABLE `user_locations`
  ADD CONSTRAINT `user_locations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

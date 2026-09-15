-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2026 at 09:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `carwash`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `is_present` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `staff_id`, `attendance_date`, `is_present`) VALUES
(1, 1, '2025-01-02', 1),
(2, 1, '2025-01-03', 1),
(3, 1, '2025-01-06', 1),
(4, 1, '2025-02-03', 1),
(5, 1, '2025-03-05', 1),
(6, 1, '2025-04-02', 1),
(7, 1, '2025-05-06', 1),
(8, 1, '2025-06-03', 1),
(9, 1, '2025-07-01', 1),
(10, 1, '2025-08-04', 1),
(11, 2, '2025-01-02', 1),
(12, 2, '2025-01-03', 1),
(13, 2, '2025-01-15', 0),
(14, 2, '2025-02-05', 1),
(15, 2, '2025-03-10', 1),
(16, 2, '2025-04-08', 1),
(17, 2, '2025-05-12', 1),
(18, 2, '2025-06-20', 0),
(19, 2, '2025-07-04', 1),
(20, 2, '2025-08-18', 1),
(21, 3, '2025-01-02', 1),
(22, 3, '2025-01-10', 1),
(23, 3, '2025-02-14', 0),
(24, 3, '2025-03-05', 1),
(25, 3, '2025-04-15', 1),
(26, 3, '2025-05-20', 1),
(27, 3, '2025-06-11', 1),
(28, 3, '2025-07-22', 0),
(29, 3, '2025-08-05', 1),
(30, 4, '2025-01-03', 1),
(31, 4, '2025-01-17', 1),
(32, 4, '2025-02-02', 0),
(33, 4, '2025-03-08', 1),
(34, 4, '2025-04-12', 1),
(35, 4, '2025-05-18', 0),
(36, 4, '2025-06-10', 1),
(37, 4, '2025-07-14', 1),
(38, 4, '2025-08-19', 1),
(39, 5, '2025-01-03', 1),
(40, 5, '2025-02-06', 1),
(41, 5, '2025-03-12', 1),
(42, 5, '2025-04-21', 0),
(43, 5, '2025-05-09', 1),
(44, 5, '2025-06-15', 1),
(45, 5, '2025-07-08', 1),
(46, 5, '2025-08-25', 0),
(47, 6, '2025-01-03', 1),
(48, 6, '2025-01-18', 0),
(49, 6, '2025-02-10', 1),
(50, 6, '2025-03-15', 1),
(51, 6, '2025-04-20', 0),
(52, 6, '2025-05-11', 1),
(53, 6, '2025-06-17', 1),
(54, 6, '2025-07-23', 1),
(55, 6, '2025-08-14', 0),
(56, 7, '2025-01-05', 1),
(57, 7, '2025-02-12', 1),
(58, 7, '2025-03-18', 0),
(59, 7, '2025-04-25', 1),
(60, 7, '2025-05-15', 1),
(61, 7, '2025-06-22', 0),
(62, 7, '2025-07-10', 1),
(63, 7, '2025-08-28', 1),
(64, 1, '2026-01-05', 1),
(65, 1, '2026-01-20', 1),
(66, 1, '2026-02-03', 1),
(67, 1, '2026-03-10', 1),
(68, 1, '2026-04-06', 1),
(69, 1, '2026-05-12', 1),
(70, 1, '2026-06-08', 1),
(71, 2, '2026-01-05', 1),
(72, 2, '2026-01-18', 1),
(73, 2, '2026-02-14', 0),
(74, 2, '2026-03-09', 1),
(75, 2, '2026-04-15', 1),
(76, 2, '2026-05-20', 1),
(77, 2, '2026-06-11', 1),
(78, 3, '2026-01-06', 1),
(79, 3, '2026-01-25', 1),
(80, 3, '2026-02-17', 1),
(81, 3, '2026-03-21', 0),
(82, 3, '2026-04-12', 1),
(83, 3, '2026-05-18', 1),
(84, 3, '2026-06-22', 1),
(85, 4, '2026-01-07', 1),
(86, 4, '2026-01-23', 1),
(87, 4, '2026-02-11', 1),
(88, 4, '2026-03-05', 0),
(89, 4, '2026-04-19', 1),
(90, 4, '2026-05-15', 1),
(91, 4, '2026-06-25', 1),
(92, 5, '2026-01-08', 1),
(93, 5, '2026-02-06', 1),
(94, 5, '2026-03-18', 1),
(95, 5, '2026-04-22', 0),
(96, 5, '2026-05-14', 1),
(97, 5, '2026-06-17', 1),
(98, 6, '2026-01-04', 1),
(99, 6, '2026-01-19', 0),
(100, 6, '2026-02-15', 1),
(101, 6, '2026-03-20', 1),
(102, 6, '2026-04-18', 0),
(103, 6, '2026-05-10', 1),
(104, 6, '2026-06-21', 1),
(105, 7, '2026-01-09', 1),
(106, 7, '2026-02-13', 1),
(107, 7, '2026-03-16', 0),
(108, 7, '2026-04-25', 1),
(109, 7, '2026-05-22', 1),
(110, 7, '2026-06-19', 1),
(111, 1, '2026-07-07', 1),
(112, 4, '2026-07-07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cust_id` int(11) NOT NULL,
  `cust_name` varchar(50) NOT NULL,
  `cust_dob` date NOT NULL,
  `cust_phonenum` int(11) NOT NULL,
  `cust_email` varchar(30) NOT NULL,
  `cust_username` varchar(50) NOT NULL,
  `cust_password` varchar(255) NOT NULL,
  `cust_gender` tinyint(1) NOT NULL COMMENT '0 = Male , 1 = Female',
  `cust_image` varchar(255) DEFAULT NULL,
  `cust_state` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cust_id`, `cust_name`, `cust_dob`, `cust_phonenum`, `cust_email`, `cust_username`, `cust_password`, `cust_gender`, `cust_image`, `cust_state`) VALUES
(1, 'Adam Rahman', '1998-05-12', 118725674, 'adam@gmail.com', 'adam123', '123456', 0, 'uploads/default.png', 'Negeri Sembilan'),
(2, 'Mikail Ramli', '1995-08-20', 1081156379, 'mikail@gmail.com', 'mikail123', '123456', 0, 'uploads/default.png', 'Selangor'),
(3, 'Rohana Abdullah', '1997-02-15', 135457623, 'rohana@gmail.com', 'rohana123', '123456', 1, 'uploads/default.png', 'Johor'),
(4, 'Gwen Stacy', '2000-11-03', 1976542533, 'gwen@gmail.com', 'gwen123', '123456', 1, 'uploads/default.png', 'Melaka'),
(5, 'Jarjit Singh', '1994-07-19', 1768997623, 'jarjit@gmail.com', 'jarjit123', '123456', 0, 'uploads/default.png', 'Kuala Lumpur');

-- --------------------------------------------------------

--
-- Table structure for table `leave_application`
--

CREATE TABLE `leave_application` (
  `leave_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `apply_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_application`
--

INSERT INTO `leave_application` (`leave_id`, `staff_id`, `leave_type`, `start_date`, `end_date`, `reason`, `apply_date`, `status`) VALUES
(1, 1, 'Annual Leave', '2025-03-10', '2025-03-12', 'Family vacation and personal matters', '2025-02-20', 'Approved'),
(2, 1, 'Emergency Leave', '2025-07-05', '2025-07-05', 'Urgent family matter', '2025-07-04', 'Approved'),
(3, 2, 'Annual Leave', '2025-02-17', '2025-02-19', 'Family holiday trip', '2025-01-25', 'Approved'),
(4, 2, 'Sick Leave', '2025-05-06', '2025-05-07', 'Medical treatment and recovery', '2025-05-05', 'Approved'),
(5, 2, 'Emergency Leave', '2025-08-20', '2025-08-20', 'Emergency personal issue', '2025-08-19', 'Pending'),
(6, 3, 'Annual Leave', '2025-04-14', '2025-04-16', 'Personal holiday', '2025-03-25', 'Approved'),
(7, 3, 'Sick Leave', '2025-06-09', '2025-06-09', 'Fever and medical checkup', '2025-06-08', 'Approved'),
(8, 3, 'Unpaid Leave', '2025-09-01', '2025-09-02', 'Personal commitment', '2025-08-20', 'Rejected'),
(9, 4, 'Annual Leave', '2025-03-21', '2025-03-22', 'Family event attendance', '2025-03-01', 'Approved'),
(10, 4, 'Sick Leave', '2025-05-15', '2025-05-16', 'Health issue', '2025-05-14', 'Approved'),
(11, 4, 'Emergency Leave', '2025-08-11', '2025-08-11', 'Urgent family situation', '2025-08-10', 'Pending'),
(12, 5, 'Annual Leave', '2025-06-23', '2025-06-25', 'Personal holiday', '2025-06-01', 'Approved'),
(13, 5, 'Sick Leave', '2025-07-15', '2025-07-15', 'Medical appointment', '2025-07-14', 'Approved'),
(14, 5, 'Emergency Leave', '2025-10-03', '2025-10-03', 'Family emergency', '2025-10-02', 'Pending'),
(15, 6, 'Sick Leave', '2025-02-10', '2025-02-11', 'Fever and flu', '2025-02-09', 'Approved'),
(16, 6, 'Annual Leave', '2025-05-20', '2025-05-22', 'Personal holiday', '2025-05-01', 'Approved'),
(17, 6, 'Unpaid Leave', '2025-09-15', '2025-09-16', 'Personal reasons', '2025-09-01', 'Pending'),
(18, 7, 'Sick Leave', '2025-03-05', '2025-03-05', 'Medical treatment', '2025-03-04', 'Approved'),
(19, 7, 'Annual Leave', '2025-07-28', '2025-07-30', 'Family trip', '2025-07-10', 'Approved'),
(20, 7, 'Emergency Leave', '2025-11-12', '2025-11-12', 'Unexpected family issue', '2025-11-11', 'Pending'),
(21, 1, 'Annual Leave', '2026-03-16', '2026-03-18', 'Family holiday trip', '2026-02-20', 'Approved'),
(22, 1, 'Emergency Leave', '2026-07-10', '2026-07-10', 'Urgent personal matter', '2026-07-09', 'Approved'),
(23, 2, 'Annual Leave', '2026-02-09', '2026-02-11', 'Family vacation', '2026-01-15', 'Approved'),
(24, 2, 'Sick Leave', '2026-04-06', '2026-04-07', 'Medical treatment and recovery', '2026-04-05', 'Approved'),
(25, 2, 'Emergency Leave', '2026-08-14', '2026-08-14', 'Urgent family situation', '2026-08-13', 'Pending'),
(26, 3, 'Annual Leave', '2026-05-04', '2026-05-06', 'Personal holiday', '2026-04-10', 'Approved'),
(27, 3, 'Sick Leave', '2026-03-12', '2026-03-12', 'Fever and medical checkup', '2026-03-11', 'Approved'),
(28, 3, 'Unpaid Leave', '2026-09-21', '2026-09-22', 'Personal commitment', '2026-09-01', 'Rejected'),
(29, 4, 'Annual Leave', '2026-06-01', '2026-06-02', 'Family event', '2026-05-10', 'Approved'),
(30, 4, 'Sick Leave', '2026-02-18', '2026-02-19', 'Health issue', '2026-02-17', 'Approved'),
(31, 4, 'Emergency Leave', '2026-09-05', '2026-09-05', 'Emergency family matter', '2026-09-04', 'Pending'),
(32, 5, 'Annual Leave', '2026-04-20', '2026-04-22', 'Personal vacation', '2026-03-25', 'Approved'),
(33, 5, 'Sick Leave', '2026-07-08', '2026-07-08', 'Medical appointment', '2026-07-07', 'Approved'),
(34, 5, 'Emergency Leave', '2026-10-12', '2026-10-12', 'Family emergency', '2026-10-11', 'Pending'),
(35, 6, 'Sick Leave', '2026-01-14', '2026-01-15', 'Flu and fever', '2026-01-13', 'Approved'),
(36, 6, 'Annual Leave', '2026-05-18', '2026-05-20', 'Family visit', '2026-05-01', 'Approved'),
(37, 6, 'Unpaid Leave', '2026-08-24', '2026-08-25', 'Personal reasons', '2026-08-10', 'Pending'),
(38, 7, 'Sick Leave', '2026-02-03', '2026-02-03', 'Medical checkup', '2026-02-02', 'Approved'),
(39, 7, 'Annual Leave', '2026-07-27', '2026-07-29', 'Family trip', '2026-07-05', 'Approved'),
(40, 7, 'Emergency Leave', '2026-11-06', '2026-11-06', 'Unexpected family issue', '2026-11-05', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `overtime`
--

CREATE TABLE `overtime` (
  `overtime_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `overtime_date` date NOT NULL,
  `hours` decimal(4,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overtime`
--

INSERT INTO `overtime` (`overtime_id`, `staff_id`, `overtime_date`, `hours`, `rate`, `total_amount`, `reason`, `status`) VALUES
(1, 1, '2025-01-25', 3.00, 10.00, 30.00, 'Weekend business inspection and operation monitoring', 'Approved'),
(2, 1, '2025-03-15', 2.50, 10.00, 25.00, 'Supervised high customer volume period', 'Approved'),
(3, 1, '2025-06-28', 4.00, 10.00, 40.00, 'Attended company maintenance discussion', 'Pending'),
(4, 2, '2025-01-18', 5.00, 10.00, 50.00, 'Managed weekend customer bookings', 'Approved'),
(5, 2, '2025-02-22', 3.00, 10.00, 30.00, 'Handled additional customer complaints', 'Approved'),
(6, 2, '2025-05-10', 4.50, 10.00, 45.00, 'Prepared monthly operation report', 'Pending'),
(7, 2, '2025-08-16', 2.00, 10.00, 20.00, 'Supervised evening car wash operations', 'Rejected'),
(8, 3, '2025-01-11', 4.00, 10.00, 40.00, 'Supervised weekend washing team', 'Approved'),
(9, 3, '2025-03-22', 6.00, 10.00, 60.00, 'Handled peak season customer workload', 'Approved'),
(10, 3, '2025-05-31', 3.50, 10.00, 35.00, 'Vehicle inspection and staff coordination', 'Pending'),
(11, 3, '2025-07-19', 5.00, 10.00, 50.00, 'Managed additional evening shift', 'Approved'),
(12, 4, '2025-01-25', 3.00, 10.00, 30.00, 'Stayed late for payment closing', 'Approved'),
(13, 4, '2025-04-05', 2.00, 10.00, 20.00, 'Handled additional cashier duties', 'Approved'),
(14, 4, '2025-06-14', 4.00, 10.00, 40.00, 'Weekend customer transaction support', 'Pending'),
(15, 5, '2025-02-15', 3.00, 10.00, 30.00, 'Handled extra booking requests', 'Approved'),
(16, 5, '2025-04-26', 2.50, 10.00, 25.00, 'Assisted customer registration during busy hours', 'Approved'),
(17, 5, '2025-07-12', 4.00, 10.00, 40.00, 'Managed late customer appointments', 'Pending'),
(18, 6, '2025-01-04', 5.00, 10.00, 50.00, 'Weekend car washing demand', 'Approved'),
(19, 6, '2025-02-08', 6.00, 10.00, 60.00, 'Heavy customer workload during promotion', 'Approved'),
(20, 6, '2025-04-19', 4.00, 10.00, 40.00, 'Additional vehicle cleaning service', 'Approved'),
(21, 6, '2025-06-21', 5.50, 10.00, 55.00, 'Public holiday customer demand', 'Pending'),
(22, 7, '2025-01-11', 4.00, 10.00, 40.00, 'Weekend washing shift', 'Approved'),
(23, 7, '2025-03-29', 5.00, 10.00, 50.00, 'Handled increased customer vehicles', 'Approved'),
(24, 7, '2025-05-17', 3.00, 10.00, 30.00, 'Assisted additional cleaning services', 'Rejected'),
(25, 7, '2025-08-09', 6.00, 10.00, 60.00, 'Long weekend operation support', 'Pending'),
(26, 1, '2026-01-24', 3.00, 10.00, 30.00, 'Supervised weekend business operations', 'Approved'),
(27, 1, '2026-04-18', 2.00, 10.00, 20.00, 'Reviewed company maintenance activities', 'Approved'),
(28, 1, '2026-06-27', 4.00, 10.00, 40.00, 'Managed high customer demand period', 'Pending'),
(29, 2, '2026-01-17', 5.00, 10.00, 50.00, 'Managed weekend customer scheduling', 'Approved'),
(30, 2, '2026-03-14', 4.00, 10.00, 40.00, 'Prepared monthly operation report', 'Approved'),
(31, 2, '2026-05-23', 3.50, 10.00, 35.00, 'Handled additional customer enquiries', 'Pending'),
(32, 2, '2026-06-20', 2.00, 10.00, 20.00, 'Evening operation supervision', 'Rejected'),
(33, 3, '2026-01-10', 4.00, 10.00, 40.00, 'Supervised weekend washing team', 'Approved'),
(34, 3, '2026-02-21', 6.00, 10.00, 60.00, 'Handled increased vehicle cleaning workload', 'Approved'),
(35, 3, '2026-04-25', 5.00, 10.00, 50.00, 'Managed public holiday operations', 'Approved'),
(36, 3, '2026-06-13', 3.00, 10.00, 30.00, 'Staff coordination and inspection duty', 'Pending'),
(37, 4, '2026-01-31', 3.00, 10.00, 30.00, 'Completed late payment closing', 'Approved'),
(38, 4, '2026-03-28', 2.50, 10.00, 25.00, 'Assisted weekend customer payments', 'Approved'),
(39, 4, '2026-05-16', 4.00, 10.00, 40.00, 'Handled increased transaction volume', 'Pending'),
(40, 5, '2026-02-07', 3.00, 10.00, 30.00, 'Managed additional customer bookings', 'Approved'),
(41, 5, '2026-04-11', 4.00, 10.00, 40.00, 'Assisted customer appointment management', 'Approved'),
(42, 5, '2026-06-06', 2.00, 10.00, 20.00, 'Handled evening customer enquiries', 'Pending'),
(43, 6, '2026-01-03', 5.00, 10.00, 50.00, 'Weekend car wash demand', 'Approved'),
(44, 6, '2026-02-14', 6.00, 10.00, 60.00, 'Promotion period vehicle cleaning workload', 'Approved'),
(45, 6, '2026-04-04', 4.00, 10.00, 40.00, 'Additional detailing service', 'Approved'),
(46, 6, '2026-06-20', 5.50, 10.00, 55.00, 'High customer volume weekend shift', 'Pending'),
(47, 7, '2026-01-24', 4.00, 10.00, 40.00, 'Weekend washing operation', 'Approved'),
(48, 7, '2026-03-07', 5.00, 10.00, 50.00, 'Assisted large vehicle cleaning orders', 'Approved'),
(49, 7, '2026-05-30', 3.00, 10.00, 30.00, 'Additional cleaning services', 'Rejected'),
(50, 7, '2026-06-27', 6.00, 10.00, 60.00, 'Long weekend customer workload', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `package_id` int(11) NOT NULL,
  `package_name` varchar(30) NOT NULL,
  `package_price` int(11) NOT NULL,
  `package_type` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package`
--

INSERT INTO `package` (`package_id`, `package_name`, `package_price`, `package_type`) VALUES
(1, 'Basic Wash', 20, 0),
(2, 'Premium Wash', 50, 0),
(3, 'Full Detailing', 120, 1),
(4, 'Ultimate Package', 200, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipt`
--

CREATE TABLE `receipt` (
  `receipt_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `receipt_date` date NOT NULL,
  `receipt_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_advance`
--

CREATE TABLE `salary_advance` (
  `advance_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `reason` text NOT NULL,
  `request_date` date NOT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_advance`
--

INSERT INTO `salary_advance` (`advance_id`, `staff_id`, `amount`, `reason`, `request_date`, `status`) VALUES
(1, 1, 2000, 'Business equipment purchase and company expenses', '2025-02-10', 'Approved'),
(2, 1, 1500, 'Emergency company maintenance expenses', '2025-07-15', 'Approved'),
(3, 2, 800, 'Family emergency expenses', '2025-03-05', 'Approved'),
(4, 2, 500, 'Personal financial commitment', '2025-08-12', 'Pending'),
(5, 3, 600, 'Vehicle repair expenses', '2025-04-18', 'Approved'),
(6, 3, 400, 'Medical expenses', '2025-09-02', 'Pending'),
(7, 4, 500, 'Family financial assistance', '2025-02-25', 'Approved'),
(8, 4, 300, 'Personal emergency expenses', '2025-07-20', 'Rejected'),
(9, 5, 400, 'Education expenses', '2025-05-08', 'Approved'),
(10, 5, 250, 'Monthly financial support', '2025-10-01', 'Pending'),
(11, 6, 300, 'Motorcycle repair expenses', '2025-03-12', 'Approved'),
(12, 6, 200, 'Family emergency', '2025-08-22', 'Pending'),
(13, 7, 250, 'Medical expenses', '2025-04-05', 'Approved'),
(14, 7, 350, 'Personal emergency expenses', '2025-11-10', 'Rejected'),
(15, 1, 2500, 'Company equipment and maintenance expenses', '2026-02-15', 'Approved'),
(16, 1, 1200, 'Emergency business expenses', '2026-07-05', 'Approved'),
(17, 2, 1000, 'Family emergency expenses', '2026-03-08', 'Approved'),
(18, 2, 600, 'Personal financial commitment', '2026-08-18', 'Pending'),
(19, 3, 700, 'Vehicle repair expenses', '2026-04-12', 'Approved'),
(20, 3, 400, 'Medical expenses', '2026-09-10', 'Pending'),
(21, 4, 500, 'Family support expenses', '2026-02-20', 'Approved'),
(22, 4, 300, 'Personal emergency needs', '2026-07-22', 'Rejected'),
(23, 5, 450, 'Education payment expenses', '2026-05-14', 'Approved'),
(24, 5, 250, 'Monthly financial assistance', '2026-10-02', 'Pending'),
(25, 6, 350, 'Motorcycle repair expenses', '2026-03-18', 'Approved'),
(26, 6, 200, 'Family emergency expenses', '2026-08-25', 'Pending'),
(27, 7, 300, 'Medical treatment expenses', '2026-04-08', 'Approved'),
(28, 7, 250, 'Personal emergency expenses', '2026-11-15', 'Rejected');

-- --------------------------------------------------------

--
-- Table structure for table `salary_summary`
--

CREATE TABLE `salary_summary` (
  `salary_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `salary_month` int(11) NOT NULL,
  `salary_year` int(11) NOT NULL,
  `allowance` int(11) DEFAULT 0,
  `deduction` int(11) DEFAULT 0,
  `bonus` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_summary`
--

INSERT INTO `salary_summary` (`salary_id`, `staff_id`, `salary_month`, `salary_year`, `allowance`, `deduction`, `bonus`) VALUES
(1, 1, 1, 2025, 500, 0, 1000),
(2, 1, 2, 2025, 500, 0, 0),
(3, 1, 3, 2025, 500, 100, 500),
(4, 1, 4, 2025, 500, 0, 0),
(5, 1, 5, 2025, 500, 0, 800),
(6, 1, 6, 2025, 500, 50, 0),
(7, 1, 7, 2025, 500, 0, 1000),
(8, 1, 8, 2025, 500, 0, 0),
(9, 2, 1, 2025, 300, 50, 200),
(10, 2, 2, 2025, 300, 0, 0),
(11, 2, 3, 2025, 300, 50, 300),
(12, 2, 4, 2025, 300, 0, 0),
(13, 2, 5, 2025, 300, 50, 200),
(14, 2, 6, 2025, 300, 0, 0),
(15, 2, 7, 2025, 300, 50, 300),
(16, 2, 8, 2025, 300, 0, 0),
(17, 3, 1, 2025, 200, 30, 100),
(18, 3, 2, 2025, 200, 0, 0),
(19, 3, 3, 2025, 200, 50, 150),
(20, 3, 4, 2025, 200, 0, 0),
(21, 3, 5, 2025, 200, 30, 100),
(22, 3, 6, 2025, 200, 0, 0),
(23, 3, 7, 2025, 200, 30, 150),
(24, 3, 8, 2025, 200, 0, 0),
(25, 4, 1, 2025, 100, 20, 0),
(26, 4, 2, 2025, 100, 0, 50),
(27, 4, 3, 2025, 100, 20, 0),
(28, 4, 4, 2025, 100, 0, 0),
(29, 4, 5, 2025, 100, 30, 50),
(30, 4, 6, 2025, 100, 0, 0),
(31, 4, 7, 2025, 100, 20, 50),
(32, 4, 8, 2025, 100, 0, 0),
(33, 5, 1, 2025, 100, 20, 0),
(34, 5, 2, 2025, 100, 0, 50),
(35, 5, 3, 2025, 100, 20, 0),
(36, 5, 4, 2025, 100, 0, 50),
(37, 5, 5, 2025, 100, 20, 0),
(38, 5, 6, 2025, 100, 0, 0),
(39, 5, 7, 2025, 100, 20, 50),
(40, 5, 8, 2025, 100, 0, 0),
(41, 6, 1, 2025, 50, 20, 0),
(42, 6, 2, 2025, 50, 0, 0),
(43, 6, 3, 2025, 50, 30, 50),
(44, 6, 4, 2025, 50, 0, 0),
(45, 6, 5, 2025, 50, 20, 0),
(46, 6, 6, 2025, 50, 0, 50),
(47, 6, 7, 2025, 50, 20, 0),
(48, 6, 8, 2025, 50, 0, 0),
(49, 7, 1, 2025, 50, 20, 0),
(50, 7, 2, 2025, 50, 0, 50),
(51, 7, 3, 2025, 50, 20, 0),
(52, 7, 4, 2025, 50, 0, 0),
(53, 7, 5, 2025, 50, 20, 50),
(54, 7, 6, 2025, 50, 0, 0),
(55, 7, 7, 2025, 50, 20, 0),
(56, 7, 8, 2025, 50, 0, 50),
(57, 1, 1, 2026, 500, 0, 1000),
(58, 1, 2, 2026, 500, 0, 0),
(59, 1, 3, 2026, 500, 100, 500),
(60, 1, 4, 2026, 500, 0, 0),
(61, 1, 5, 2026, 500, 0, 800),
(62, 1, 6, 2026, 500, 50, 0),
(63, 2, 1, 2026, 300, 50, 200),
(64, 2, 2, 2026, 300, 0, 0),
(65, 2, 3, 2026, 300, 50, 300),
(66, 2, 4, 2026, 300, 0, 0),
(67, 2, 5, 2026, 300, 50, 200),
(68, 2, 6, 2026, 300, 0, 0),
(69, 3, 1, 2026, 200, 30, 100),
(70, 3, 2, 2026, 200, 0, 0),
(71, 3, 3, 2026, 200, 50, 150),
(72, 3, 4, 2026, 200, 0, 0),
(73, 3, 5, 2026, 200, 30, 100),
(74, 3, 6, 2026, 200, 0, 0),
(75, 4, 1, 2026, 100, 20, 0),
(76, 4, 2, 2026, 100, 0, 50),
(77, 4, 3, 2026, 100, 20, 0),
(78, 4, 4, 2026, 100, 0, 0),
(79, 4, 5, 2026, 100, 30, 50),
(80, 4, 6, 2026, 100, 0, 0),
(81, 5, 1, 2026, 100, 20, 0),
(82, 5, 2, 2026, 100, 0, 50),
(83, 5, 3, 2026, 100, 20, 0),
(84, 5, 4, 2026, 100, 0, 50),
(85, 5, 5, 2026, 100, 20, 0),
(86, 5, 6, 2026, 100, 0, 0),
(87, 6, 1, 2026, 50, 20, 0),
(88, 6, 2, 2026, 50, 0, 0),
(89, 6, 3, 2026, 50, 30, 50),
(90, 6, 4, 2026, 50, 0, 0),
(91, 6, 5, 2026, 50, 20, 0),
(92, 6, 6, 2026, 50, 0, 50),
(93, 7, 1, 2026, 50, 20, 0),
(94, 7, 2, 2026, 50, 0, 50),
(95, 7, 3, 2026, 50, 20, 0),
(96, 7, 4, 2026, 50, 0, 0),
(97, 7, 5, 2026, 50, 20, 50),
(98, 7, 6, 2026, 50, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `service_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `service_time` time NOT NULL,
  `service_status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`service_id`, `cust_id`, `package_id`, `vehicle_id`, `service_date`, `service_time`, `service_status`) VALUES
(1, 1, 1, 1, '2026-07-07', '08:30:00', 'Pending'),
(2, 2, 2, 2, '2026-07-07', '09:30:00', 'Confirmed'),
(3, 3, 3, 3, '2026-07-07', '10:30:00', 'Pending'),
(4, 4, 2, 4, '2026-07-07', '12:00:00', 'Cancelled'),
(5, 5, 4, 5, '2026-07-07', '14:00:00', 'Confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `staff_name` varchar(50) NOT NULL,
  `staff_email` varchar(100) DEFAULT NULL,
  `staff_password` varchar(255) DEFAULT NULL,
  `staff_phonenum` int(11) NOT NULL,
  `staff_dob` date NOT NULL,
  `staff_state` varchar(50) NOT NULL,
  `staff_hiredate` date NOT NULL,
  `staff_salary` int(11) NOT NULL,
  `staff_job` varchar(20) NOT NULL,
  `staff_gender` tinyint(1) NOT NULL,
  `staff_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `staff_name`, `staff_email`, `staff_password`, `staff_phonenum`, `staff_dob`, `staff_state`, `staff_hiredate`, `staff_salary`, `staff_job`, `staff_gender`, `staff_image`) VALUES
(1, 'Kamal Shah Bin Puteh', 'kamal.shah@gmail.com', '123', 123456789, '1985-03-15', 'Selangor', '2020-01-10', 5000, 'Owner', 1, 'uploads/default.png'),
(2, 'Waeyoh Binti Wan Muda', 'waeyoh.manager@gmail.com', '123', 134567890, '1990-07-22', 'Kelantan', '2021-03-15', 3500, 'Manager', 0, 'uploads/default.png'),
(3, 'Shafril Haq Bin Kamal Shah', 'shafril.supervisor@gmail.com', '123', 145678901, '1995-11-08', 'Selangor', '2022-05-20', 2800, 'Supervisor', 1, 'uploads/default.png'),
(4, 'Muhammad Ferhan Bin Muriddan', 'fmuriddan@gmail.com', '123', 167890123, '1998-02-18', 'Johor', '2023-01-05', 2200, 'Cashier', 1, 'uploads/default.png'),
(5, 'Muhammad Faris Aiman Bin Mohammad Fauzan', 'faris.aiman@gmail.com', '123', 178901234, '2000-06-25', 'Kuala Lumpur', '2023-06-12', 2200, 'Receptionist', 1, 'uploads/default.png'),
(6, 'Muhammad Irfan Bin Fazli', 'irfan.fazli@gmail.com', '123', 189012345, '2001-09-10', 'Perak', '2024-02-01', 1800, 'Car Washer', 1, 'uploads/default.png'),
(7, 'Abu Zar Bin Shahrum', 'abuzar.shahrum@gmail.com', '123', 190123456, '1999-12-30', 'Penang', '2024-04-15', 1800, 'Car Washer', 1, 'uploads/default.png');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `vehicle_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `vehicle_platenum` varchar(20) NOT NULL,
  `vehicle_brand` varchar(15) NOT NULL,
  `vehicle_model` varchar(15) NOT NULL,
  `vehicle_type` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`vehicle_id`, `cust_id`, `vehicle_platenum`, `vehicle_brand`, `vehicle_model`, `vehicle_type`) VALUES
(1, 1, 'VTA565', 'Toyota', 'Fortuner', 'Jeep'),
(2, 2, 'SB45366G', 'Honda', 'Civic', 'Car'),
(3, 3, 'MCS1890', 'Perodua', 'Myvi', 'Car'),
(4, 4, '100P', 'Proton', 'X50', 'Car'),
(5, 5, 'SU45234P', 'BMW', '320i', 'Car');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cust_id`);

--
-- Indexes for table `leave_application`
--
ALTER TABLE `leave_application`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `overtime`
--
ALTER TABLE `overtime`
  ADD PRIMARY KEY (`overtime_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `package`
--
ALTER TABLE `package`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `cust_id` (`cust_id`),
  ADD KEY `receipt_id` (`receipt_id`);

--
-- Indexes for table `receipt`
--
ALTER TABLE `receipt`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `staff_id` (`staff_id`,`vehicle_id`,`package_id`);

--
-- Indexes for table `salary_advance`
--
ALTER TABLE `salary_advance`
  ADD PRIMARY KEY (`advance_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `salary_summary`
--
ALTER TABLE `salary_summary`
  ADD PRIMARY KEY (`salary_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `cust_id` (`cust_id`,`package_id`,`vehicle_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD KEY `cust_id` (`cust_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `cust_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_application`
--
ALTER TABLE `leave_application`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `overtime`
--
ALTER TABLE `overtime`
  MODIFY `overtime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `package`
--
ALTER TABLE `package`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `salary_advance`
--
ALTER TABLE `salary_advance`
  MODIFY `advance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `salary_summary`
--
ALTER TABLE `salary_summary`
  MODIFY `salary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicle`
--
ALTER TABLE `vehicle`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `leave_application`
--
ALTER TABLE `leave_application`
  ADD CONSTRAINT `leave_application_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `overtime`
--
ALTER TABLE `overtime`
  ADD CONSTRAINT `overtime_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `salary_advance`
--
ALTER TABLE `salary_advance`
  ADD CONSTRAINT `salary_advance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `salary_summary`
--
ALTER TABLE `salary_summary`
  ADD CONSTRAINT `salary_summary_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

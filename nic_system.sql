-- =============================================
-- NIC System Database Schema
-- =============================================

CREATE DATABASE IF NOT EXISTS `nic_system`;
USE `nic_system`;

-- =============================================
-- Table: admins (Login & Register)
-- =============================================
CREATE TABLE IF NOT EXISTS `admins` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- Table: nic_records (National ID Records)
-- =============================================
CREATE TABLE IF NOT EXISTS `nic_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nic_number` VARCHAR(20) NOT NULL UNIQUE,
    `full_name` VARCHAR(150) NOT NULL,
    `dob` DATE NOT NULL,
    `gender` VARCHAR(10) NOT NULL,
    `marital_status` VARCHAR(30) DEFAULT NULL,
    `citizenship` VARCHAR(50) DEFAULT NULL,
    `mother_name` VARCHAR(150) DEFAULT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `phone_number` VARCHAR(20) DEFAULT NULL,
    `occupation` VARCHAR(100) DEFAULT NULL,
    `additional_info` TEXT DEFAULT NULL,
    `photo_path` VARCHAR(255) DEFAULT NULL,
    `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `date_updated` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*
SQLyog Ultimate v10.00 Beta1
MySQL - 5.5.5-10.4.17-MariaDB : Database - managex_site
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`managex_site` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `managex_site`;

/*Table structure for table `business_types` */

DROP TABLE IF EXISTS `business_types`;

CREATE TABLE `business_types` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `clients` */

DROP TABLE IF EXISTS `clients`;

CREATE TABLE `clients` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `testimonial` varchar(350) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `installation_year` date DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `logo` (`logo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Table structure for table `customers` */

DROP TABLE IF EXISTS `customers`;

CREATE TABLE `customers` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `business_type` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1006 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `discounts` */

DROP TABLE IF EXISTS `discounts`;

CREATE TABLE `discounts` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `fraction` decimal(3,2) NOT NULL,
  `expiry` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `downloads` */

DROP TABLE IF EXISTS `downloads`;

CREATE TABLE `downloads` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `country_name` varchar(255) NOT NULL,
  `referrer` varchar(300) DEFAULT NULL,
  `customer` mediumint(9) NOT NULL,
  `plan` mediumint(9) NOT NULL,
  `order` mediumint(9) DEFAULT NULL,
  `status` enum('PENDING','COMPLETED','ABANDONED') NOT NULL DEFAULT 'PENDING',
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `inquiries` */

DROP TABLE IF EXISTS `inquiries`;

CREATE TABLE `inquiries` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Table structure for table `order_discounts` */

DROP TABLE IF EXISTS `order_discounts`;

CREATE TABLE `order_discounts` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `order` mediumint(9) NOT NULL,
  `discount` mediumint(9) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `plan` mediumint(9) NOT NULL,
  `customer` mediumint(9) NOT NULL,
  `expiry` timestamp NULL DEFAULT current_timestamp(),
  `invoice_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) NOT NULL,
  `download_id` mediumint(9) DEFAULT NULL,
  `tracking_id` varchar(255) NOT NULL,
  `merchant_ref` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_merchant_ref` (`merchant_ref`),
  UNIQUE KEY `uidx_tracking_id` (`tracking_id`),
  KEY `plan` (`plan`),
  KEY `customer` (`customer`) USING BTREE,
  KEY `download_id` (`download_id`),
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`plan`) REFERENCES `plans` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1149 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `payment_requests` */

DROP TABLE IF EXISTS `payment_requests`;

CREATE TABLE `payment_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `creation_date` timestamp NULL DEFAULT current_timestamp(),
  `request_email` varchar(80) DEFAULT NULL,
  `request_mgx_code` varchar(80) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` float(11,2) DEFAULT NULL,
  `is_paid` varchar(3) DEFAULT 'no',
  `tracking_id` varchar(255) DEFAULT NULL,
  `merchant_ref` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `payments` */

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_email` varchar(20) DEFAULT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `reference_code` varchar(20) DEFAULT NULL,
  `generated_secret_code` varchar(16) DEFAULT NULL,
  `managex_code` varchar(8) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `amount_paid` float(11,2) DEFAULT NULL,
  `payment_hash` text DEFAULT NULL,
  `payment_retrieved` varchar(3) DEFAULT 'no',
  `retriever_profile` text DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `plan_pricing` */

DROP TABLE IF EXISTS `plan_pricing`;

CREATE TABLE `plan_pricing` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `plan` mediumint(9) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_frequency` enum('MONTHLY','YEARLY','ONETIME') DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `plan` (`plan`),
  CONSTRAINT `plan_pricing_ibfk_1` FOREIGN KEY (`plan`) REFERENCES `plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

/*Table structure for table `plans` */

DROP TABLE IF EXISTS `plans`;

CREATE TABLE `plans` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `plan_color` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

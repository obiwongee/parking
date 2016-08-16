-- phpMyAdmin SQL Dump
-- version 4.0.10.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 16, 2016 at 04:34 PM
-- Server version: 5.6.31-0ubuntu0.15.10.1
-- PHP Version: 5.6.11-1ubuntu3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `parking`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE IF NOT EXISTS `cars` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` enum('small','medium','large','super_sized') NOT NULL,
  `license_plate` varchar(7) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `license_place` (`license_plate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE IF NOT EXISTS `fees` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `half_hour` decimal(5,2) NOT NULL,
  `max_daily` decimal(5,2) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `half_hour`, `max_daily`, `created`) VALUES
(1, 1.00, 7.00, '2016-07-01 00:00:00'),
(2, 2.00, 15.00, '2016-08-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `parking_lots`
--

CREATE TABLE IF NOT EXISTS `parking_lots` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `capacity` int(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `parking_lots`
--

INSERT INTO `parking_lots` (`id`, `name`, `capacity`) VALUES
(1, 'Toronto East', 100),
(2, 'Toronto West', 25);

-- --------------------------------------------------------

--
-- Table structure for table `parking_spots`
--

CREATE TABLE IF NOT EXISTS `parking_spots` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parking_lot_id` int(10) NOT NULL,
  `car_id` int(10) NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime DEFAULT NULL,
  `duration` int(3) DEFAULT NULL,
  `amount` float(4,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


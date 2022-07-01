-- Adminer 4.7.8 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `RTSoft-test`;
CREATE DATABASE `RTSoft-test` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `RTSoft-test`;

DROP TABLE IF EXISTS `CATEGORY`;
CREATE TABLE `CATEGORY` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `CATEGORY` (`id`, `name`) VALUES
(1,	'Elektronika'),
(2,	'Dílna'),
(3,	'Domácnost'),
(4,	'Ostatní');

DROP TABLE IF EXISTS `ITEM`;
CREATE TABLE `ITEM` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `price` float NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `publish_date` date NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_category_idx` (`category_id`),
  CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `CATEGORY` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ITEM` (`id`, `name`, `price`, `category_id`, `publish_date`, `active`) VALUES
(1,	'Šroubovák',	79.99,	2,	'2022-06-07',	1),
(2,	'Monitor',	2499.99,	1,	'2022-05-30',	1),
(3,	'Nabíječka',	249.99,	1,	'2022-06-26',	0),
(4,	'Houba',	29.99,	1,	'2022-06-02',	0),
(5,	'Jablko',	9.9,	NULL,	'2022-07-15',	0),
(6,	'Propisovací pero',	10.01,	NULL,	'2022-07-23',	0),
(7,	'SD karta',	229.99,	1,	'2022-06-21',	0),
(8,	'Kalkulačka',	549.99,	1,	'2022-06-28',	1),
(9,	'Nalepovací štítky',	19.991,	NULL,	'2022-06-18',	0),
(10,	'Mobilní telefon',	6999.99,	1,	'2022-07-21',	0),
(11,	'Vizitka',	8.49,	NULL,	'2022-06-16',	1),
(12,	'USB-C kabel 1m',	159.99,	1,	'2022-06-30',	1),
(13,	'Sluchátka',	349.99,	1,	'2022-04-13',	0);

DROP TABLE IF EXISTS `TAG`;
CREATE TABLE `TAG` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) CHARACTER SET utf8mb4 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `TAG` (`id`, `name`) VALUES
(1,	'Novinka'),
(2,	'Výprodej'),
(3,	'Technika'),
(4,	'Kancelář'),
(5,	'Potraviny');

-- 2022-07-01 07:30:13

DROP TABLE IF EXISTS `ITEM_TAG`;
CREATE TABLE `ITEM_TAG` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tag_idx` (`tag_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `fk_item` FOREIGN KEY (`item_id`) REFERENCES `ITEM` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_tag` FOREIGN KEY (`tag_id`) REFERENCES `TAG` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `ITEM_TAG` (`id`, `item_id`, `tag_id`) VALUES
(4,	8,	1),
(5,	8,	2),
(9,	12,	1),
(18,	5,	1),
(19,	5,	2),
(20,	6,	1),
(22,	4,	2),
(23,	13,	1),
(24,	13,	2),
(25,	11,	1),
(26,	11,	2),
(32,	2,	1),
(33,	9,	4),
(34,	1,	1),
(35,	1,	2),
(36,	1,	3);

SET NAMES utf8mb4;
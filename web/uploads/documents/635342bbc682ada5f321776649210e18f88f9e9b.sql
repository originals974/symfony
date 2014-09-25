-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Lun 28 Juillet 2014 à 07:36
-- Version du serveur: 5.6.19
-- Version de PHP: 5.4.30-1~dotdeb.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `dictonary`
--

-- --------------------------------------------------------

--
-- Structure de la table `field_category`
--

CREATE TABLE IF NOT EXISTS `field_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `technical_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_order` int(11) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index_field_category_display_name` (`display_name`),
  UNIQUE KEY `unique_index_field_category_technical_name` (`technical_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Contenu de la table `field_category`
--

INSERT INTO `field_category` (`id`, `technical_name`, `display_name`, `display_order`, `is_enabled`) VALUES
(7, 'text', 'Texte', 1, 1),
(8, 'choice', 'Liste de choix', 2, 1),
(9, 'datetime', 'Date et heure', 3, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

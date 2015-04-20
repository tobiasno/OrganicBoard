SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Table structure for table `cron_dates`
--

CREATE TABLE IF NOT EXISTS `cron_dates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `last_purge` datetime NOT NULL,
  `last_topic_index` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cron_dates`
--

INSERT INTO `cron_dates` (`id`, `last_purge`, `last_topic_index`) VALUES
(1, '2015-01-01 00:00:00', '2015-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `author` varchar(32) NOT NULL,
  `headline` varchar(128) NOT NULL,
  `content` text NOT NULL,
  `topic` varchar(20) NOT NULL,
  `last_reply` datetime NOT NULL,
  `number_of_replies` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_content` (`content`(128))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `replies`
--

CREATE TABLE IF NOT EXISTS `replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_ref` bigint(20) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `author` varchar(32) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reply` (`post_ref`,`author`,`content`(64))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `topics`
--

CREATE TABLE IF NOT EXISTS `topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic` varchar(64) NOT NULL,
  `number_of_occurrences` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

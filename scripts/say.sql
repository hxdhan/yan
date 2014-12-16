-- phpMyAdmin SQL Dump
-- version 4.2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2014-12-16 09:02:06
-- 服务器版本： 5.1.62-community
-- PHP Version: 5.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `say`
--

-- --------------------------------------------------------

--
-- 表的结构 `category`
--

CREATE TABLE IF NOT EXISTS `category` (
`category_id` int(10) unsigned NOT NULL,
  `category_name` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `category_type` tinyint(3) unsigned DEFAULT NULL,
  `category_desc` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_url` char(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `small_imgurl` char(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `message_count` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `web_url` char(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- 表的结构 `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
`comment_id` int(10) unsigned NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `comment_userid` int(10) unsigned NOT NULL,
  `voice_url` char(100) COLLATE utf8_unicode_ci NOT NULL,
  `image_url` char(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(10) unsigned NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `text` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `touser_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1019 ;

-- --------------------------------------------------------

--
-- 表的结构 `like`
--

CREATE TABLE IF NOT EXISTS `like` (
`like_id` int(10) unsigned NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `like_userid` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1376 ;

-- --------------------------------------------------------

--
-- 表的结构 `message`
--

CREATE TABLE IF NOT EXISTS `message` (
`message_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `voice_url` char(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `duration` int(10) unsigned NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `smile_id` int(10) unsigned NOT NULL DEFAULT '0',
  `like_count` int(10) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(10) unsigned NOT NULL DEFAULT '0',
  `receive_count` int(10) unsigned NOT NULL DEFAULT '0',
  `original_message_id` int(10) unsigned NOT NULL DEFAULT '0',
  `image_url` char(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image_color` char(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `new_comment` int(11) NOT NULL DEFAULT '0',
  `new_like` int(11) NOT NULL DEFAULT '0',
  `new_time` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `platform` char(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `wall_id` int(10) unsigned NOT NULL DEFAULT '0',
  `wall_name` char(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1563 ;

-- --------------------------------------------------------

--
-- 表的结构 `msgmark`
--

CREATE TABLE IF NOT EXISTS `msgmark` (
`mark_id` int(10) unsigned NOT NULL,
  `image_url` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `wall_id` int(10) unsigned NOT NULL,
  `info` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

-- --------------------------------------------------------

--
-- 表的结构 `msgshare`
--

CREATE TABLE IF NOT EXISTS `msgshare` (
`share_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `platform` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `msgwall`
--

CREATE TABLE IF NOT EXISTS `msgwall` (
`wall_id` int(10) unsigned NOT NULL,
  `owner_userid` int(10) unsigned NOT NULL,
  `name` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `info` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `image_url` char(127) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `web_url` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `radius` decimal(9,6) NOT NULL,
  `type` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=52 ;

-- --------------------------------------------------------

--
-- 表的结构 `msgwallfavourates`
--

CREATE TABLE IF NOT EXISTS `msgwallfavourates` (
`favourate_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `wall_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `push_sequence`
--

CREATE TABLE IF NOT EXISTS `push_sequence` (
  `push_id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `report`
--

CREATE TABLE IF NOT EXISTS `report` (
`report_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message_id` int(10) unsigned NOT NULL,
  `reason_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `repreason`
--

CREATE TABLE IF NOT EXISTS `repreason` (
`id` int(10) unsigned NOT NULL,
  `reason` varchar(32) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- 表的结构 `smile`
--

CREATE TABLE IF NOT EXISTS `smile` (
  `smile_id` tinyint(3) unsigned NOT NULL,
  `smile_name` char(10) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`user_id` int(10) unsigned NOT NULL,
  `qq_token` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `wx_token` char(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qq_id` char(16) COLLATE utf8_unicode_ci NOT NULL,
  `cellphone` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `push_registration` char(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=292 ;

-- --------------------------------------------------------

--
-- 表的结构 `userencounter`
--

CREATE TABLE IF NOT EXISTS `userencounter` (
`encounter_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `encounter_userid` int(10) unsigned NOT NULL,
  `newtouser` tinyint(4) NOT NULL DEFAULT '0',
  `newtoencounteruser` tinyint(4) NOT NULL DEFAULT '0',
  `message_id` text COLLATE utf8_unicode_ci NOT NULL,
  `time` text COLLATE utf8_unicode_ci NOT NULL,
  `last_time` int(10) unsigned NOT NULL,
  `encounter_time` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=296 ;

-- --------------------------------------------------------

--
-- 表的结构 `userfollow`
--

CREATE TABLE IF NOT EXISTS `userfollow` (
`follow_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `follow_userid` int(10) unsigned NOT NULL,
  `newtouser` tinyint(4) NOT NULL DEFAULT '1',
  `newtofollowuser` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1328 ;

-- --------------------------------------------------------

--
-- 表的结构 `userinfo`
--

CREATE TABLE IF NOT EXISTS `userinfo` (
  `user_id` int(10) unsigned NOT NULL,
  `photo_url` char(100) COLLATE utf8_unicode_ci NOT NULL,
  `photo_color` char(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nickname` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `gender` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `birthday` int(11) NOT NULL,
  `description` char(100) COLLATE utf8_unicode_ci NOT NULL,
  `reg_time` int(10) unsigned DEFAULT NULL,
  `last_login_time` int(10) unsigned DEFAULT NULL,
  `last_message_time` int(10) unsigned DEFAULT NULL,
  `last_like_time` int(10) unsigned DEFAULT NULL,
  `last_comment_time` int(10) unsigned DEFAULT NULL,
  `last_chat_time` int(10) unsigned DEFAULT NULL,
  `expert_type` int(10) unsigned NOT NULL DEFAULT '0',
  `grade` int(10) unsigned NOT NULL DEFAULT '1',
  `point` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `usrchat`
--

CREATE TABLE IF NOT EXISTS `usrchat` (
`chat_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `receive_userid` int(10) unsigned NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `chat_content` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `duration` int(10) unsigned NOT NULL DEFAULT '0',
  `voice_listened` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `content_type` tinyint(3) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `new` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=652 ;

-- --------------------------------------------------------

--
-- 表的结构 `usrexpert_type`
--

CREATE TABLE IF NOT EXISTS `usrexpert_type` (
`expert_id` int(10) unsigned NOT NULL,
  `expert_name` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `count` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `usrfriend`
--

CREATE TABLE IF NOT EXISTS `usrfriend` (
`friend_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `friend_userid` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=577 ;

-- --------------------------------------------------------

--
-- 表的结构 `usrfriendinvite`
--

CREATE TABLE IF NOT EXISTS `usrfriendinvite` (
`invite_id` int(10) unsigned NOT NULL,
  `inviter_userid` int(10) unsigned NOT NULL,
  `invitee_userid` int(10) unsigned NOT NULL,
  `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `longitude` decimal(9,6) NOT NULL,
  `latitude` decimal(9,6) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `usrgradepoint`
--

CREATE TABLE IF NOT EXISTS `usrgradepoint` (
`grade` int(10) unsigned NOT NULL,
  `point` int(10) unsigned NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- 表的结构 `usrnotification`
--

CREATE TABLE IF NOT EXISTS `usrnotification` (
`notif_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `active_userid` int(10) unsigned NOT NULL,
  `type` char(16) COLLATE utf8_unicode_ci NOT NULL,
  `message_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL,
  `new` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=627 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
 ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
 ADD PRIMARY KEY (`comment_id`), ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `like`
--
ALTER TABLE `like`
 ADD PRIMARY KEY (`like_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
 ADD PRIMARY KEY (`message_id`), ADD KEY `latitude` (`latitude`,`longitude`), ADD KEY `category_id` (`category_id`), ADD KEY `author_id` (`author_id`), ADD KEY `wall_id` (`wall_id`);

--
-- Indexes for table `msgmark`
--
ALTER TABLE `msgmark`
 ADD PRIMARY KEY (`mark_id`);

--
-- Indexes for table `msgshare`
--
ALTER TABLE `msgshare`
 ADD PRIMARY KEY (`share_id`);

--
-- Indexes for table `msgwall`
--
ALTER TABLE `msgwall`
 ADD PRIMARY KEY (`wall_id`), ADD KEY `longitude` (`longitude`,`latitude`), ADD KEY `owner_userid` (`owner_userid`);

--
-- Indexes for table `msgwallfavourates`
--
ALTER TABLE `msgwallfavourates`
 ADD PRIMARY KEY (`favourate_id`), ADD KEY `user_id` (`user_id`), ADD KEY `wall_id` (`wall_id`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
 ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `repreason`
--
ALTER TABLE `repreason`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `smile`
--
ALTER TABLE `smile`
 ADD PRIMARY KEY (`smile_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `userencounter`
--
ALTER TABLE `userencounter`
 ADD PRIMARY KEY (`encounter_id`), ADD KEY `user_id` (`user_id`), ADD KEY `encounter_userid` (`encounter_userid`);

--
-- Indexes for table `userfollow`
--
ALTER TABLE `userfollow`
 ADD PRIMARY KEY (`follow_id`), ADD KEY `user_id` (`user_id`), ADD KEY `follow_userid` (`follow_userid`);

--
-- Indexes for table `userinfo`
--
ALTER TABLE `userinfo`
 ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `usrchat`
--
ALTER TABLE `usrchat`
 ADD PRIMARY KEY (`chat_id`);

--
-- Indexes for table `usrexpert_type`
--
ALTER TABLE `usrexpert_type`
 ADD PRIMARY KEY (`expert_id`);

--
-- Indexes for table `usrfriend`
--
ALTER TABLE `usrfriend`
 ADD PRIMARY KEY (`friend_id`);

--
-- Indexes for table `usrfriendinvite`
--
ALTER TABLE `usrfriendinvite`
 ADD PRIMARY KEY (`invite_id`);

--
-- Indexes for table `usrgradepoint`
--
ALTER TABLE `usrgradepoint`
 ADD PRIMARY KEY (`grade`);

--
-- Indexes for table `usrnotification`
--
ALTER TABLE `usrnotification`
 ADD PRIMARY KEY (`notif_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
MODIFY `category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
MODIFY `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1019;
--
-- AUTO_INCREMENT for table `like`
--
ALTER TABLE `like`
MODIFY `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1376;
--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
MODIFY `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1563;
--
-- AUTO_INCREMENT for table `msgmark`
--
ALTER TABLE `msgmark`
MODIFY `mark_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT for table `msgshare`
--
ALTER TABLE `msgshare`
MODIFY `share_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `msgwall`
--
ALTER TABLE `msgwall`
MODIFY `wall_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=52;
--
-- AUTO_INCREMENT for table `msgwallfavourates`
--
ALTER TABLE `msgwallfavourates`
MODIFY `favourate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
MODIFY `report_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `repreason`
--
ALTER TABLE `repreason`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=292;
--
-- AUTO_INCREMENT for table `userencounter`
--
ALTER TABLE `userencounter`
MODIFY `encounter_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=296;
--
-- AUTO_INCREMENT for table `userfollow`
--
ALTER TABLE `userfollow`
MODIFY `follow_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1328;
--
-- AUTO_INCREMENT for table `usrchat`
--
ALTER TABLE `usrchat`
MODIFY `chat_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=652;
--
-- AUTO_INCREMENT for table `usrexpert_type`
--
ALTER TABLE `usrexpert_type`
MODIFY `expert_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `usrfriend`
--
ALTER TABLE `usrfriend`
MODIFY `friend_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=577;
--
-- AUTO_INCREMENT for table `usrfriendinvite`
--
ALTER TABLE `usrfriendinvite`
MODIFY `invite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `usrgradepoint`
--
ALTER TABLE `usrgradepoint`
MODIFY `grade` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `usrnotification`
--
ALTER TABLE `usrnotification`
MODIFY `notif_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=627;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

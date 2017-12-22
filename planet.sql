-- phpMyAdmin SQL Dump
-- version 4.7.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 22, 2017 at 12:07 AM
-- Server version: 5.7.20
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `planet`
--

-- --------------------------------------------------------

--
-- Table structure for table `LikesToProjects`
--

CREATE TABLE `LikesToProjects` (
  `RowID` int(11) NOT NULL,
  `ProjectID` bigint(20) NOT NULL,
  `UserID` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Projects`
--

CREATE TABLE `Projects` (
  `ProjectID` bigint(20) NOT NULL,
  `UserID` bigint(20) NOT NULL,
  `ProjectName` varchar(50) NOT NULL,
  `ProjectDescription` varchar(1000) NOT NULL,
  `ProjectSearchKeywords` text NOT NULL,
  `ProjectData` longtext NOT NULL,
  `ProjectImage` longtext,
  `ProjectIsMusicBlocks` tinyint(1) NOT NULL,
  `ProjectCreatorName` varchar(50) NOT NULL DEFAULT 'anonymous',
  `ProjectCreatedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ProjectDownloads` int(11) NOT NULL DEFAULT '0',
  `ProjectLikes` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Tags`
--

CREATE TABLE `Tags` (
  `TagID` int(11) NOT NULL,
  `TagName` varchar(50) NOT NULL,
  `IsTagUserAddable` tinyint(1) NOT NULL COMMENT 'Can the user add the tag to a project?',
  `IsDisplayTag` tinyint(1) NOT NULL COMMENT 'Should the tag be displayed on the ''sort by'' menu? (i.e. is the tag important)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `TagsToProjects`
--

CREATE TABLE `TagsToProjects` (
  `RowID` int(11) NOT NULL,
  `TagID` int(11) NOT NULL,
  `ProjectID` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `LikesToProjects`
--
ALTER TABLE `LikesToProjects`
  ADD PRIMARY KEY (`RowID`);

--
-- Indexes for table `Projects`
--
ALTER TABLE `Projects`
  ADD PRIMARY KEY (`ProjectID`);

--
-- Indexes for table `Tags`
--
ALTER TABLE `Tags`
  ADD PRIMARY KEY (`TagID`);

--
-- Indexes for table `TagsToProjects`
--
ALTER TABLE `TagsToProjects`
  ADD PRIMARY KEY (`RowID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `LikesToProjects`
--
ALTER TABLE `LikesToProjects`
  MODIFY `RowID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Tags`
--
ALTER TABLE `Tags`
  MODIFY `TagID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `TagsToProjects`
--
ALTER TABLE `TagsToProjects`
  MODIFY `RowID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

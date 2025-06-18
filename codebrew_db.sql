-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 18, 2025 at 09:10 AM
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
-- Database: `codebrew_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `llm_generatedcontent`
--

CREATE TABLE `llm_generatedcontent` (
  `content_id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `generated_by` varchar(100) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `llm_interactionlog`
--

CREATE TABLE `llm_interactionlog` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `input` text DEFAULT NULL,
  `output` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `llm_model`
--

CREATE TABLE `llm_model` (
  `model_id` int(11) NOT NULL,
  `version` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `text` text DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `llm_feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quiz_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_premium_only` tinyint(1) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `llm_generated` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendation`
--

CREATE TABLE `recommendation` (
  `recommendation_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `tittle` varchar(255) DEFAULT NULL,
  `url` text DEFAULT NULL,
  `llm_score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT NULL,
  `xp_total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `password`, `is_premium`, `xp_total`) VALUES
(1, 'ikhsan', 'ikhsan987@example.com', '$2y$10$frhE86R1IJIi2TNqcZl7Yerv0o2XdiHRk6.W.9sgvznutc8Ba3Q8O', 0, 0),
(2, 'adil', 'adil678@example.com', '$2y$10$ExxVsZgT7dVhw4pnqV3gQOKtGhiCV3oMbpw1G1OZ/6UVETx2jp1h6', 0, 0),
(3, 'alif', 'alif101@example.com', '$2y$10$zAcR7V8vSRJPaD0MKOUHy.sV7vPOgn5RT28zInuUXQUe.kGfNeMfu', 0, 0),
(4, 'bintang', 'bintang202@example.com', '$2y$10$kD0m5bb6Ebz7f9aZk8AG2ehKHiIib7RuQrrAkM1oWBo7kaUgXak32', 1, 50),
(5, 'cindy', 'cindy303@example.com', '$2y$10$L3rZxQ0HyoNH9jcKk57Vfeh2skPb1VuKn3V57TMrxbRLlIYOwG7fC', 0, 120),
(6, 'dika', 'dika404@example.com', '$2y$10$uIvXT6BZwGZdeyaRJx.CyOdZHyKzKCRVpKg78y7JpBy/NaqWaYz2u', 1, 70),
(7, 'elsa', 'elsa505@example.com', '$2y$10$g5K/f0FB6C7lXB6PaNz9e.i9KeUyol6rbEk5uXQ8hv2OQ.y2EAtfW', 0, 15),
(8, 'ferdi', 'ferdi606@example.com', '$2y$10$g3xxM1HUCcexYZqP8V0iP.lKYKp9nmHEos9KhYqQKyf3hCo9tGCYG', 1, 100),
(9, 'gina', 'gina707@example.com', '$2y$10$IB4QtzExMFKNL9khfUY1lu0nd32bcZnW5pASaTbrR4zCW.6fHjE.q', 0, 40),
(10, 'hadi', 'hadi808@example.com', '$2y$10$J3On/N9T8ZBkLgA7A/epdOtBdMldVqSvU3pM.mjEw.q2z6YVOi1VO', 1, 60),
(11, 'intan', 'intan909@example.com', '$2y$10$zA9.ZYNY5d9ORn2MywBv2OTcPov9CrQyTIShppPzvjPP7yZxzOQuG', 0, 30),
(12, 'jack', 'jack010@example.com', '$2y$10$Z54Tqb/dGB.ZZ9RCfqzHQeNHuOl7UghvAfRA0sZxB2T9Pz/UBG2Bi', 1, 80),
(13, 'karen', 'karen111@example.com', '$2y$10$Z.kXkZxWTvntRAcmDAtFg.N0M.IGj9cRZghwEXynsLRKd8UQeNzK2', 0, 55),
(14, 'lisa', 'lisa212@example.com', '$2y$10$B2t30TSXcSuqflSpXyyr..ZGvjg2cZSnM2PL6KH9Xy5CEoF1ccrpW', 0, 90),
(15, 'miko', 'miko313@example.com', '$2y$10$gr8VqLUgUOjNQ6WUXS4Oa.OOwdLuN19jRISeBJcGJ8Mdc7J0zgbhe', 0, 110),
(16, 'nina', 'nina414@example.com', '$2y$10$M.VNhmWmW8gQ8Bd/ZGP68eh9CmX9NLXUBITWxltrA3nA9aRYjw1aa', 1, 0),
(17, 'okta', 'okta515@example.com', '$2y$10$yFt68mc9zvvVr2Rrbf.0u.fDbLk6dEyAyZwTzZPThY93NAAeaD8Ae', 0, 65),
(18, 'putra', 'putra616@example.com', '$2y$10$AAN0jdzZhRxLHZBt1dGuKO2ySkO49duKLFwFGZfPF2qb1aqLD9wA.', 1, 20),
(19, 'qiana', 'qiana717@example.com', '$2y$10$zDHL9K/1u7Y1Avz8Zj4pduWc7R/OvjW98YZ6qlYaRAUxnWrCq8K3e', 0, 35),
(20, 'rafa', 'rafa818@example.com', '$2y$10$QGQ/x7rhNDnRMCRcAcxUeOBkKzMLb5LUYNgZCU2lH0QmDkGIfszM.', 1, 140),
(21, 'salsa', 'salsa919@example.com', '$2y$10$tI7Q.JFu02IRBWYbqFykYexkqcbWWMP0Ai8NU1asxjOmKoZOp0mAy', 0, 10),
(22, 'tari', 'tari020@example.com', '$2y$10$QovE37SFSHRAMmSlBLXWHeLR5gVG/qk5nXFTGhT8S2XHgUoTqvs0S', 1, 88),
(23, 'udin', 'udin121@example.com', '$2y$10$TkU8RcE8lZnNQon1ZTu1ie5q6h2BkuvbKD5nK4EF9UED/dCZJdUEu', 0, 77),
(24, 'viona', 'viona222@example.com', '$2y$10$3ItTXKRP51NRoKn6NmYoIel27J9BR74vTQaz9S6A2V6CnDps/FIZ2', 1, 45),
(25, 'wahyu', 'wahyu323@example.com', '$2y$10$5NT41kP5VZREtvYgSmIzE.LXkFCH.wJS6mUI6UAZsmkVzzS11pjje', 0, 95),
(26, 'xena', 'xena424@example.com', '$2y$10$ES5wX5CjBaP4Ph3QrFgKSeK4kGtrIzfy9UOVNsytAMnJgq2P20AzO', 1, 66),
(27, 'yusuf', 'yusuf525@example.com', '$2y$10$VWoz.RVJpCSf4Cu3SLjw0OU4d30.mR7hJ80JPZkSAYtjq6aBPnIf2', 0, 60),
(28, 'zara', 'zara626@example.com', '$2y$10$Z8UlIsuMH78yyo1IY7ZmA.5btynOqQ9AIjqS6L1AsLdRrQ3OAL7G6', 1, 33),
(29, 'niko', 'niko727@example.com', '$2y$10$CH3QIRZ93LJ0pIoMkp1CNO8DCPuXyfjS8A5n51k9t/JcHqkM8DZWa', 0, 70),
(30, 'lara', 'lara828@example.com', '$2y$10$tTqvCN.b5OdYKP9KnWGE8O5HGRIlvNaMd7U75Qp3ptPhRJ8cwtsXK', 1, 120),
(31, 'reza', 'reza929@example.com', '$2y$10$ZxIrc5B1zCBfLbFHHjXjWuCVFxzmOBhXlPTp2KlZ8ZBquVjfyTJvC', 0, 105);

-- --------------------------------------------------------

--
-- Table structure for table `useranswer`
--

CREATE TABLE `useranswer` (
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `selected_answer` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `llm_analysis` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` int(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_quiz`
--

CREATE TABLE `user_quiz` (
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `completion_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `llm_generatedcontent`
--
ALTER TABLE `llm_generatedcontent`
  ADD PRIMARY KEY (`content_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `llm_interactionlog`
--
ALTER TABLE `llm_interactionlog`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indexes for table `llm_model`
--
ALTER TABLE `llm_model`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `recommendation`
--
ALTER TABLE `recommendation`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `useranswer`
--
ALTER TABLE `useranswer`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_quiz`
--
ALTER TABLE `user_quiz`
  ADD PRIMARY KEY (`user_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `llm_generatedcontent`
--
ALTER TABLE `llm_generatedcontent`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `llm_interactionlog`
--
ALTER TABLE `llm_interactionlog`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `llm_model`
--
ALTER TABLE `llm_model`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recommendation`
--
ALTER TABLE `recommendation`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `useranswer`
--
ALTER TABLE `useranswer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `llm_generatedcontent`
--
ALTER TABLE `llm_generatedcontent`
  ADD CONSTRAINT `llm_generatedcontent_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`);

--
-- Constraints for table `llm_interactionlog`
--
ALTER TABLE `llm_interactionlog`
  ADD CONSTRAINT `llm_interactionlog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `llm_interactionlog_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `llm_model` (`model_id`);

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`);

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `recommendation`
--
ALTER TABLE `recommendation`
  ADD CONSTRAINT `recommendation_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `useranswer`
--
ALTER TABLE `useranswer`
  ADD CONSTRAINT `useranswer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `useranswer_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `question` (`question_id`);

--
-- Constraints for table `user_quiz`
--
ALTER TABLE `user_quiz`
  ADD CONSTRAINT `user_quiz_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `user_quiz_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

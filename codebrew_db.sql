-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 21, 2025 at 04:51 AM
-- Server version: 8.0.35
-- PHP Version: 8.2.20

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
  `category_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int NOT NULL,
  `session_id` varchar(50) NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `sender` enum('user','bot') NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `session_id`, `user_id`, `content`, `sender`, `timestamp`) VALUES
(1, 'chat_6855afb9bd283', 33, 'halo', 'user', '2025-06-21 02:00:11'),
(2, 'chat_6855b0b700697', 33, 'halo', 'user', '2025-06-21 02:04:24'),
(3, 'chat_6855b0b700697', 33, 'Hello! How can I assist you today?', 'bot', '2025-06-21 02:04:25'),
(4, 'chat_6855b0b700697', 33, 'sekarang jam berapa ya?', 'user', '2025-06-21 02:04:31'),
(5, 'chat_6855b0b700697', 33, 'Sekarang jam berapa ya?\n\nSaya telah melakukan pencarian dan perhitungan untuk mengetahui waktu saat ini. Namun, saya tidak memiliki akses langsung ke waktu saat ini karena saya adalah model bahasa yang berjalan pada server yang terletak di suatu tempat di dunia.\n\nNamun, berdasarkan informasi yang saya dapatkan dari pencarian, saya dapat memberikan beberapa contoh waktu saat ini di beberapa kota besar di dunia.\n\nMisalnya, waktu saat ini di New York adalah 9:04 AM pada tanggal 20 Juni 2025, dan waktu saat ini di Jakarta adalah 6:38 AM pada tanggal 20 Juni 2025.\n\nJika Anda ingin mengetahui waktu saat ini di lokasi Anda, saya sarankan Anda untuk memeriksa jam di perangkat Anda atau mencari informasi waktu saat ini di internet.\n\nSaya harap jawaban ini membantu!', 'bot', '2025-06-21 02:04:50'),
(6, 'chat_6855b303af7d5', 34, 'haloo', 'user', '2025-06-21 02:14:13'),
(7, 'chat_6855b303af7d5', 34, 'Hallo! How can I help you today?', 'bot', '2025-06-21 02:14:15'),
(8, 'chat_6855b303af7d5', 34, 'siapakah anda?', 'user', '2025-06-21 02:14:18'),
(9, 'chat_6855b303af7d5', 34, 'Saya adalah Compound-Beta, sebuah sistem yang dibangun oleh Groq, sebuah perusahaan yang menyediakan inferensi AI yang cepat melalui perangkat keras LPU (Large Language Processing Unit) yang mereka kembangkan sendiri. Saya dirancang untuk membantu menjawab pertanyaan dan melakukan tugas-tugas yang memerlukan pemrosesan bahasa alami. Apa yang bisa saya bantu untuk Anda hari ini?', 'bot', '2025-06-21 02:14:19'),
(10, 'chat_6855b684b980b', 34, 'halo', 'user', '2025-06-21 02:29:11'),
(11, 'chat_6855b684b980b', 34, 'Halo! Selamat datang! Saya CodeBrew Assistant, asisten AI yang siap membantu kamu dengan pertanyaan seputar coding. Ada yang bisa saya bantu hari ini?', 'bot', '2025-06-21 02:29:12'),
(12, 'chat_6855b684b980b', 34, 'siapakah anda', 'user', '2025-06-21 02:29:18'),
(13, 'chat_6855b684b980b', 34, 'Saya adalah CodeBrew Assistant, asisten AI yang dirancang khusus untuk membantu kamu dengan pertanyaan seputar coding, seperti HTML, CSS, JavaScript, Python, PHP, dan MySQL. Saya ada di sini untuk membantu kamu menyelesaikan masalah coding dan memberikan informasi yang kamu butuhkan.', 'bot', '2025-06-21 02:29:19'),
(14, 'chat_6855b684b980b', 34, 'anda dapat membantu saya dengan apa saja?', 'user', '2025-06-21 02:29:29'),
(15, 'chat_6855b684b980b', 34, 'Saya dapat membantu kamu dengan berbagai topik seputar coding, seperti:\n\n* HTML: membuat struktur web, menggunakan tag, atribut, dan properti\n* CSS: styling web, menggunakan selector, properti, dan nilai\n* JavaScript: membuat interaktivitas web, menggunakan variabel, fungsi, dan event\n* Python: membuat program, menggunakan sintaks, fungsi, dan library\n* PHP: membuat aplikasi web, menggunakan sintaks, fungsi, dan integrasi dengan database\n* MySQL: membuat dan mengelola database, menggunakan query, tabel, dan relasi\n\nSaya juga dapat membantu kamu dengan:\n\n* Debugging kode\n* Membuat contoh kode\n* Menjelaskan konsep-konsep coding\n* Memberikan saran dan rekomendasi\n\nApa yang kamu butuhkan bantuan hari ini?', 'bot', '2025-06-21 02:29:30'),
(16, 'chat_6855b684b980b', 34, 'looping di python itu apa sih?', 'user', '2025-06-21 02:29:46'),
(17, 'chat_6855b684b980b', 34, 'Looping di Python adalah cara untuk mengulang kode tertentu beberapa kali. Ada dua jenis looping di Python:\n\n1. **For Loop**: digunakan untuk mengulang kode untuk setiap item dalam sebuah iterabel (seperti list, tuple, atau string).\nContoh:\n```python\nbuah = [\'apel\', \'banana\', \'ceri\']\nfor buahnya in buah:\n    print(buahnya)\n```\nOutput:\n```\napel\nbanana\nceri\n```\n2. **While Loop**: digunakan untuk mengulang kode selama kondisi tertentu masih benar.\nContoh:\n```python\ni = 0\nwhile i < 5:\n    print(i)\n    i += 1\n```\nOutput:\n```\n0\n1\n2\n3\n4\n```\nLooping sangat berguna untuk mengulang tugas-tugas yang sama, seperti mengolah data dalam sebuah list atau membuat program yang berjalan terus menerus sampai kondisi tertentu terpenuhi.', 'bot', '2025-06-21 02:29:47'),
(18, 'chat_68560a40b8342', 33, 'halo', 'user', '2025-06-21 08:26:26'),
(19, 'chat_68560a40b8342', 33, 'Halo! Selamat datang! Saya CodeBrew Assistant, asisten AI yang siap membantu kamu dengan pertanyaan seputar coding. Ada yang bisa saya bantu hari ini?', 'bot', '2025-06-21 08:26:29'),
(20, 'chat_68560a40b8342', 33, 'apa saja yang bisa anda lakukan?', 'user', '2025-06-21 08:26:40'),
(21, 'chat_68560a40b8342', 33, 'Saya dapat membantu dengan berbagai topik seputar coding, seperti:\n\n* HTML: struktur, atribut, tag, dan contoh kode\n* CSS: styling, layout, selector, dan contoh kode\n* JavaScript: sintaks, fungsi, variabel, dan contoh kode\n* Python: sintaks, fungsi, modul, dan contoh kode\n* PHP: sintaks, fungsi, variabel, dan contoh kode\n* MySQL: query, database, tabel, dan contoh kode\n\nSaya juga dapat membantu dengan:\n\n* Debugging kode\n* Mencari solusi untuk masalah coding\n* Memberikan contoh kode untuk proyek tertentu\n* Menjelaskan konsep-konsep coding yang sulit dipahami\n\nTanya saya apa saja yang kamu butuhkan!', 'bot', '2025-06-21 08:26:41'),
(22, 'chat_68560a40b8342', 33, 'css itu fungsinya apa?', 'user', '2025-06-21 08:26:50'),
(23, 'chat_68560a40b8342', 33, 'CSS (Cascading Style Sheets) adalah bahasa styling yang digunakan untuk mengontrol tampilan dan layout halaman web. Fungsinya adalah:\n\n* Mengatur warna, font, dan ukuran teks\n* Mengatur layout dan posisi elemen-elemen pada halaman web\n* Mengatur background, border, dan efek visual lainnya\n* Mengatur responsifitas halaman web agar terlihat bagus di berbagai perangkat\n\nDengan CSS, kamu dapat membuat halaman web terlihat lebih menarik dan profesional tanpa harus mengubah kode HTML. CSS juga memungkinkan kamu untuk membuat tema dan gaya yang konsisten di seluruh situs web.', 'bot', '2025-06-21 08:26:51'),
(24, 'chat_685612fb367e5', 33, 'halo', 'user', '2025-06-21 09:03:41'),
(25, 'chat_685612fb367e5', 33, 'Halo! Saya CodeBrew Assistant, asisten AI yang siap membantu kamu dengan pertanyaan seputar coding. Ada yang bisa saya bantu hari ini?', 'bot', '2025-06-21 09:03:42'),
(26, 'chat_685612fb367e5', 33, 'html itu apa sih?', 'user', '2025-06-21 09:03:50'),
(27, 'chat_685612fb367e5', 33, 'HTML (HyperText Markup Language) adalah bahasa markup yang digunakan untuk membuat struktur dan konten pada halaman web. HTML digunakan untuk membuat elemen-elemen seperti judul, paragraf, gambar, link, tabel, dan lain-lain pada sebuah halaman web. HTML adalah dasar dari pembuatan website dan biasanya digunakan bersama dengan CSS (untuk styling) dan JavaScript (untuk interaktivitas).', 'bot', '2025-06-21 09:03:51'),
(28, 'chat_68562d222fdde', 34, 'apa kabar', 'user', '2025-06-21 10:55:17'),
(29, 'chat_68562d222fdde', 34, 'Halo! Kabar baik, terima kasih! Siap membantu dengan pertanyaan coding kamu. Ada yang bisa saya bantu hari ini?', 'bot', '2025-06-21 10:55:19'),
(30, 'chat_6856311eddeb3', 35, 'hai', 'user', '2025-06-21 11:12:16'),
(31, 'chat_6856311eddeb3', 35, 'Hai! Saya CodeBrew Assistant, asisten AI yang siap membantu Anda dengan pertanyaan seputar coding, seperti HTML, CSS, JavaScript, Python, PHP, dan MySQL. Silakan bertanya!', 'bot', '2025-06-21 11:12:17');

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `session_id` varchar(50) NOT NULL,
  `user_id` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_sessions`
--

INSERT INTO `chat_sessions` (`session_id`, `user_id`, `start_time`, `end_time`) VALUES
('chat_6855a976d7b7f', 1, '2025-06-21 01:33:26', NULL),
('chat_6855a9fc9fd80', 1, '2025-06-21 01:35:40', NULL),
('chat_6855aabb6c0f3', 1, '2025-06-21 01:38:51', NULL),
('chat_6855ac86d0e10', 1, '2025-06-21 01:46:30', NULL),
('chat_6855add915f0a', 1, '2025-06-21 01:52:09', NULL),
('chat_6855ae5975696', 1, '2025-06-21 01:54:17', NULL),
('chat_6855aec58c2b6', 1, '2025-06-21 01:56:05', NULL),
('chat_6855af3c66293', 1, '2025-06-21 01:58:04', NULL),
('chat_6855afb9bd283', 1, '2025-06-21 02:00:09', NULL),
('chat_6855b0b700697', 1, '2025-06-21 02:04:23', NULL),
('chat_6855b303af7d5', 1, '2025-06-21 02:14:11', NULL),
('chat_6855b684b980b', 1, '2025-06-21 02:29:08', NULL),
('chat_68560a40b8342', 1, '2025-06-21 08:26:24', NULL),
('chat_685612fb367e5', 1, '2025-06-21 09:03:39', NULL),
('chat_685617cf211a9', 1, '2025-06-21 09:24:15', NULL),
('chat_68562d222fdde', 1, '2025-06-21 10:55:14', NULL),
('chat_6856311eddeb3', 1, '2025-06-21 11:12:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `llm_generatedcontent`
--

CREATE TABLE `llm_generatedcontent` (
  `content_id` int NOT NULL,
  `quiz_id` int DEFAULT NULL,
  `question_text` text COLLATE utf8mb4_general_ci,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `generated_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `llm_interactionlog`
--

CREATE TABLE `llm_interactionlog` (
  `log_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `model_id` int DEFAULT NULL,
  `input` text COLLATE utf8mb4_general_ci,
  `output` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `llm_model`
--

CREATE TABLE `llm_model` (
  `model_id` int NOT NULL,
  `version` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `active` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `user_id`, `order_number`, `payment_method`, `amount`, `payment_status`, `created_at`) VALUES
(1, 35, 'PINTAR-6856312B1F034', 'qris', 0.00, 'pending', '2025-06-21 04:12:27'),
(2, 35, 'PINTAR-6856319E39419', 'qris', 0.00, 'pending', '2025-06-21 04:14:22'),
(3, 35, 'PINTAR-685631A411F73', 'qris', 0.00, 'pending', '2025-06-21 04:14:28'),
(4, 35, 'PINTAR-685631BA674AA', 'qris', 0.00, 'pending', '2025-06-21 04:14:50'),
(5, 35, 'PINTAR-685631D31FA4A', 'gopay', 0.00, 'pending', '2025-06-21 04:15:15'),
(6, 35, 'PINTAR-685631D908AFA', 'ovo', 0.00, 'pending', '2025-06-21 04:15:21'),
(7, 35, 'PINTAR-685631DB53398', 'bankTransfer', 0.00, 'pending', '2025-06-21 04:15:23'),
(8, 35, 'PINTAR-685631DDAA592', 'bankTransfer', 0.00, 'pending', '2025-06-21 04:15:25'),
(9, 35, 'PINTAR-685631E00FEAA', 'ovo', 0.00, 'pending', '2025-06-21 04:15:28'),
(10, 35, 'PINTAR-6856323E88204', 'gopay', 0.00, 'pending', '2025-06-21 04:17:02'),
(11, 35, 'PINTAR-6856327B93729', 'qris', 0.00, 'pending', '2025-06-21 04:18:03'),
(12, 35, 'PINTAR-6856327E036EF', 'qris', 0.00, 'pending', '2025-06-21 04:18:06'),
(13, 35, 'PINTAR-685632C8259E8', 'gopay', 0.00, 'pending', '2025-06-21 04:19:20'),
(14, 35, 'PINTAR-6856334647E84', 'qris', 0.00, 'pending', '2025-06-21 04:21:26'),
(15, 35, 'PINTAR-6856339E4CBA6', 'qris', 0.00, 'pending', '2025-06-21 04:22:54'),
(16, 35, 'PINTAR-685633A352960', 'qris', 0.00, 'pending', '2025-06-21 04:22:59'),
(17, 35, 'PINTAR-685633AC0DFEF', 'qris', 0.00, 'pending', '2025-06-21 04:23:08'),
(18, 35, 'PINTAR-68563465EE42A', 'qris', 0.00, 'pending', '2025-06-21 04:26:13'),
(19, 35, 'PINTAR-685634726A201', 'qris', 0.00, 'pending', '2025-06-21 04:26:26'),
(20, 35, 'PINTAR-685634775B76C', 'qris', 0.00, 'pending', '2025-06-21 04:26:31'),
(21, 35, 'PINTAR-6856347BEA229', 'qris', 0.00, 'pending', '2025-06-21 04:26:35'),
(22, 35, 'PINTAR-6856347EDFB87', 'qris', 0.00, 'pending', '2025-06-21 04:26:38'),
(23, 35, 'PINTAR-6856348036164', 'qris', 0.00, 'pending', '2025-06-21 04:26:40'),
(24, 35, 'PINTAR-68563483BC32E', 'qris', 0.00, 'completed', '2025-06-21 04:26:43'),
(25, 36, 'PINTAR-6856352514F71', 'qris', 0.00, 'completed', '2025-06-21 04:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `question_id` int NOT NULL,
  `quiz_id` int DEFAULT NULL,
  `text` text COLLATE utf8mb4_general_ci,
  `correct_answer` text COLLATE utf8mb4_general_ci,
  `llm_feedback` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quiz_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_premium_only` tinyint(1) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `llm_generated` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendation`
--

CREATE TABLE `recommendation` (
  `recommendation_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `tittle` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `url` text COLLATE utf8mb4_general_ci,
  `llm_score` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_premium` tinyint(1) DEFAULT NULL,
  `xp_total` int DEFAULT NULL
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
(31, 'reza', 'reza929@example.com', '$2y$10$ZxIrc5B1zCBfLbFHHjXjWuCVFxzmOBhXlPTp2KlZ8ZBquVjfyTJvC', 0, 105),
(32, 'riki', '23@students.uii.ac.id', '$2y$10$SZ8nDt5tFo/Hqlr9Idb0Du7.PXLR3T7xgoJ/Bju5nhk9p2Mx1mC8y', 0, 0),
(33, 'apa', 'apa@gmail.com', '$2y$10$1zZiVqZ0vjL0Zuar7oD9HOW7GEv.x5uII5.zzdinBoqhNIzrgjJHi', 0, 0),
(34, 'rikii', 'rikii@gmail.com', '$2y$10$TV7viFiYPmb3VCYFz0tOeOMa6M43LaYJ7NXFXCVI8Q0jbosUY.Zme', 1, 0),
(35, 'tes1', 'tes1@gmail.com', '$2y$10$FGvlDpNAqhTSYNZDlY9oeOH8f5phQj6Vqi3Us5b5u.AZu90n.eH7m', 1, 0),
(36, 'tes2', 'tes2@gmail.com', '$2y$10$EGN1iDu8XTDALfqAJmkPI.uC3BymhRoOo/8ZgnnjghoAFoRieBKi.', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `useranswer`
--

CREATE TABLE `useranswer` (
  `answer_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `selected_answer` text COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `llm_analysis` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` int NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_quiz`
--

CREATE TABLE `user_quiz` (
  `user_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` int DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `completion_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `llm_generatedcontent`
--
ALTER TABLE `llm_generatedcontent`
  MODIFY `content_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `llm_interactionlog`
--
ALTER TABLE `llm_interactionlog`
  MODIFY `log_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `llm_model`
--
ALTER TABLE `llm_model`
  MODIFY `model_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `question_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quiz_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recommendation`
--
ALTER TABLE `recommendation`
  MODIFY `recommendation_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `useranswer`
--
ALTER TABLE `useranswer`
  MODIFY `answer_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`session_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

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
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

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

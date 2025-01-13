-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 13 jan 2025 om 12:19
-- Serverversie: 10.4.32-MariaDB
-- PHP-versie: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_reservation`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `table_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `party_size` int(11) NOT NULL,
  `status` enum('pending','confirmed','denied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `tables`
--

CREATE TABLE `tables` (
  `table_id` int(11) NOT NULL,
  `table_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('available','reserved') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `tables`
--

INSERT INTO `tables` (`table_id`, `table_number`, `capacity`, `status`, `created_at`) VALUES
(10, '1', 5, 'available', '2025-01-12 17:05:48'),
(11, '2', 5, 'available', '2025-01-12 17:05:54'),
(12, '3', 8, 'available', '2025-01-12 17:06:00'),
(13, '4', 8, 'available', '2025-01-12 17:06:07'),
(14, '5', 12, 'available', '2025-01-12 17:06:13'),
(15, '6', 1, 'available', '2025-01-12 17:06:18');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'ibo', 'admin', '2025-01-12 15:35:52'),
(2, 'ibo', '$2y$10$R1kr6FAFU.Svvb4jEjbh1OemGoaXwusPqnx/fD1mKeJwoGCT8Tj3C', 'admin', '2025-01-12 16:33:52');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexen voor tabel `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`table_id`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT voor een tabel `tables`
--
ALTER TABLE `tables`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`table_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

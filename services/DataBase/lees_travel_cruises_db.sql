-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 20-06-2026 a las 23:36:31
-- Versión del servidor: 8.0.44
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lees_travel_cruises_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog_multimedia`
--

CREATE TABLE `blog_multimedia` (
  `Id_Multimedia` varchar(36) NOT NULL,
  `Id_Post` varchar(36) NOT NULL,
  `Url_File` varchar(255) NOT NULL,
  `File_Type` enum('image','video') NOT NULL,
  `Updated_At` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Is_Deleted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog_post`
--

CREATE TABLE `blog_post` (
  `Id_Post` varchar(36) NOT NULL,
  `Id_Trip` int NOT NULL,
  `Id_User` int NOT NULL,
  `Title` varchar(200) NOT NULL,
  `Description` text NOT NULL,
  `Latitude` decimal(10,8) DEFAULT NULL,
  `Longitude` decimal(11,8) DEFAULT NULL,
  `Published_Date` datetime DEFAULT CURRENT_TIMESTAMP,
  `Updated_At` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Is_Deleted` tinyint(1) DEFAULT '0',
  `Moderation_Status` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  `Is_Public` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bookings`
--

CREATE TABLE `bookings` (
  `Id_Booking` int NOT NULL,
  `Id_Purchaser_User` int NOT NULL,
  `Booking_Date` datetime DEFAULT CURRENT_TIMESTAMP,
  `Booking_Status` enum('Confirmada','Cancelada') DEFAULT 'Confirmada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `bookings`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `daily_itinerary`
--

CREATE TABLE `daily_itinerary` (
  `Id_Itinerary` int NOT NULL,
  `Id_Trip` int NOT NULL,
  `Day_Number` int NOT NULL,
  `Port_of_Call` varchar(100) DEFAULT NULL,
  `Activity_Description` text,
  `Arrival_Time` time DEFAULT NULL,
  `Departure_Time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `daily_itinerary`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `history`
--

CREATE TABLE `history` (
  `Id_History` int NOT NULL,
  `Id_Passenger` int NOT NULL,
  `Event_Description` text NOT NULL,
  `Event_Date` datetime DEFAULT CURRENT_TIMESTAMP,
  `Id_Guia_User` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marketing_promos`
--

CREATE TABLE `marketing_promos` (
  `Id_Promo` int NOT NULL,
  `Id_Trip` int NOT NULL,
  `Title_Offer` varchar(100) DEFAULT NULL,
  `Description` text,
  `Image_Banner` varchar(255) DEFAULT NULL,
  `Action_Link` varchar(255) DEFAULT NULL,
  `Special_Price_USD` decimal(10,2) DEFAULT NULL,
  `Start_Date` date DEFAULT NULL,
  `Expiration_Date` date DEFAULT NULL,
  `Is_Active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `marketing_promos`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medical_records`
--

CREATE TABLE `medical_records` (
  `Id_File` int NOT NULL,
  `Id_Traveler` int NOT NULL,
  `Blood_Type` varchar(5) NOT NULL,
  `Allergies` text,
  `Chronic_Diseases` text,
  `Current_Medication` text,
  `Observations` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `medical_records`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `Id_Notification` int NOT NULL,
  `Id_User` int NOT NULL,
  `Id_Sender_User` int DEFAULT NULL,
  `Type` enum('Info','Warning','Emergency','Promo') DEFAULT 'Info',
  `Title` varchar(100) NOT NULL,
  `Message` text NOT NULL,
  `Is_Read` tinyint(1) DEFAULT '0',
  `Created_At` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `notifications`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `passenger`
--

CREATE TABLE `passenger` (
  `Id_Passenger` int NOT NULL,
  `Id_Booking` int NOT NULL,
  `Id_Traveler` int NOT NULL,
  `Id_Trip` int NOT NULL,
  `Boarding_Status` enum('Por Abordar','Abordado','No Se Presentó') DEFAULT 'Por Abordar',
  `Cabin_Number` varchar(15) DEFAULT NULL,
  `Special_Assistance` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `passenger`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `quotes`
--

CREATE TABLE `quotes` (
  `Id_Quote` int NOT NULL,
  `Full_Name` varchar(150) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Phone` varchar(50) NOT NULL,
  `Destination` varchar(150) NOT NULL,
  `Travel_Date` date NOT NULL,
  `Passengers` int NOT NULL,
  `Cabin_Type` varchar(50) NOT NULL,
  `Comments` text,
  `Status` enum('Pendiente','Atendido') DEFAULT 'Pendiente',
  `Created_At` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `quotes`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_users`
--

CREATE TABLE `system_users` (
  `Id_User` int NOT NULL,
  `Id_Traveler` int NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Photo_Path` varchar(255) DEFAULT 'default_user.png',
  `Access_Role` enum('Admin','Guia','Pasajero','Asesor') DEFAULT 'Pasajero',
  `User_Status` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `system_users`
--


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `travelers`
--

CREATE TABLE `travelers` (
  `Id_Traveler` int NOT NULL,
  `First_Name` varchar(50) NOT NULL,
  `Last_Name` varchar(50) NOT NULL,
  `Birth_Date` date NOT NULL,
  `Gender` enum('M','F','Otro') NOT NULL,
  `Nationality` varchar(50) NOT NULL,
  `Document_Type` enum('DNI','PAS','CE') NOT NULL,
  `Id_Card_Passport` varchar(25) NOT NULL,
  `Emergency_Contact` varchar(100) DEFAULT NULL,
  `Emergency_Phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `travelers`
--



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trips`
--

CREATE TABLE `trips` (
  `Id_Trip` int NOT NULL,
  `Destination_Name` varchar(100) NOT NULL,
  `Ship_Name` varchar(100) NOT NULL,
  `Cruise_Line` varchar(50) DEFAULT NULL,
  `Departure_Port` varchar(100) NOT NULL,
  `Arrival_Port` varchar(100) NOT NULL,
  `Trip_Photo` varchar(255) DEFAULT 'default_trip.png',
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Status` enum('Programado','En Curso','Finalizado','Cancelado') DEFAULT 'Programado',
  `Max_Capacity` int NOT NULL,
  `Requires_Visa` tinyint(1) DEFAULT '1',
  `Includes_Flight` tinyint(1) DEFAULT '0',
  `Duration_Nights` int DEFAULT NULL,
  `Description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `trips`


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trip_reviews`
--

CREATE TABLE `trip_reviews` (
  `Id_Review` int NOT NULL,
  `Id_Trip` int NOT NULL,
  `Id_User` int NOT NULL,
  `Rating` tinyint DEFAULT NULL,
  `Comment` text,
  `Review_Date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `blog_multimedia`
--
ALTER TABLE `blog_multimedia`
  ADD PRIMARY KEY (`Id_Multimedia`),
  ADD KEY `Id_Post` (`Id_Post`);

--
-- Indices de la tabla `blog_post`
--
ALTER TABLE `blog_post`
  ADD PRIMARY KEY (`Id_Post`),
  ADD KEY `idx_status_public` (`Moderation_Status`,`Is_Public`),
  ADD KEY `Id_User` (`Id_User`),
  ADD KEY `Id_Trip` (`Id_Trip`);

--
-- Indices de la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`Id_Booking`),
  ADD KEY `Id_Purchaser_User` (`Id_Purchaser_User`);

--
-- Indices de la tabla `daily_itinerary`
--
ALTER TABLE `daily_itinerary`
  ADD PRIMARY KEY (`Id_Itinerary`),
  ADD KEY `Id_Trip` (`Id_Trip`);

--
-- Indices de la tabla `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`Id_History`),
  ADD KEY `Id_Passenger` (`Id_Passenger`),
  ADD KEY `Id_Guia_User` (`Id_Guia_User`);

--
-- Indices de la tabla `marketing_promos`
--
ALTER TABLE `marketing_promos`
  ADD PRIMARY KEY (`Id_Promo`),
  ADD KEY `Id_Trip` (`Id_Trip`);

--
-- Indices de la tabla `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`Id_File`),
  ADD UNIQUE KEY `Id_Traveler` (`Id_Traveler`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`Id_Notification`),
  ADD KEY `Id_User` (`Id_User`),
  ADD KEY `Id_Sender_User` (`Id_Sender_User`);

--
-- Indices de la tabla `passenger`
--
ALTER TABLE `passenger`
  ADD PRIMARY KEY (`Id_Passenger`),
  ADD KEY `Id_Booking` (`Id_Booking`),
  ADD KEY `Id_Traveler` (`Id_Traveler`),
  ADD KEY `Id_Trip` (`Id_Trip`);

--
-- Indices de la tabla `quotes`
--
ALTER TABLE `quotes`
  ADD PRIMARY KEY (`Id_Quote`);

--
-- Indices de la tabla `system_users`
--
ALTER TABLE `system_users`
  ADD PRIMARY KEY (`Id_User`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `Id_Traveler` (`Id_Traveler`);

--
-- Indices de la tabla `travelers`
--
ALTER TABLE `travelers`
  ADD PRIMARY KEY (`Id_Traveler`),
  ADD UNIQUE KEY `Id_Card_Passport` (`Id_Card_Passport`),
  ADD KEY `idx_passport` (`Id_Card_Passport`);

--
-- Indices de la tabla `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`Id_Trip`);

--
-- Indices de la tabla `trip_reviews`
--
ALTER TABLE `trip_reviews`
  ADD PRIMARY KEY (`Id_Review`),
  ADD KEY `Id_Trip` (`Id_Trip`),
  ADD KEY `Id_User` (`Id_User`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bookings`
--
ALTER TABLE `bookings`
  MODIFY `Id_Booking` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `daily_itinerary`
--
ALTER TABLE `daily_itinerary`
  MODIFY `Id_Itinerary` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `history`
--
ALTER TABLE `history`
  MODIFY `Id_History` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `marketing_promos`
--
ALTER TABLE `marketing_promos`
  MODIFY `Id_Promo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `Id_File` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `Id_Notification` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `passenger`
--
ALTER TABLE `passenger`
  MODIFY `Id_Passenger` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `quotes`
--
ALTER TABLE `quotes`
  MODIFY `Id_Quote` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `system_users`
--
ALTER TABLE `system_users`
  MODIFY `Id_User` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `travelers`
--
ALTER TABLE `travelers`
  MODIFY `Id_Traveler` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `trips`
--
ALTER TABLE `trips`
  MODIFY `Id_Trip` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `trip_reviews`
--
ALTER TABLE `trip_reviews`
  MODIFY `Id_Review` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `blog_multimedia`
--
ALTER TABLE `blog_multimedia`
  ADD CONSTRAINT `blog_multimedia_ibfk_1` FOREIGN KEY (`Id_Post`) REFERENCES `blog_post` (`Id_Post`) ON DELETE CASCADE;

--
-- Filtros para la tabla `blog_post`
--
ALTER TABLE `blog_post`
  ADD CONSTRAINT `blog_post_ibfk_1` FOREIGN KEY (`Id_User`) REFERENCES `system_users` (`Id_User`),
  ADD CONSTRAINT `blog_post_ibfk_2` FOREIGN KEY (`Id_Trip`) REFERENCES `trips` (`Id_Trip`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`Id_Purchaser_User`) REFERENCES `system_users` (`Id_User`);

--
-- Filtros para la tabla `daily_itinerary`
--
ALTER TABLE `daily_itinerary`
  ADD CONSTRAINT `daily_itinerary_ibfk_1` FOREIGN KEY (`Id_Trip`) REFERENCES `trips` (`Id_Trip`) ON DELETE CASCADE;

--
-- Filtros para la tabla `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `history_ibfk_1` FOREIGN KEY (`Id_Passenger`) REFERENCES `passenger` (`Id_Passenger`) ON DELETE CASCADE,
  ADD CONSTRAINT `history_ibfk_2` FOREIGN KEY (`Id_Guia_User`) REFERENCES `system_users` (`Id_User`);

--
-- Filtros para la tabla `marketing_promos`
--
ALTER TABLE `marketing_promos`
  ADD CONSTRAINT `marketing_promos_ibfk_1` FOREIGN KEY (`Id_Trip`) REFERENCES `trips` (`Id_Trip`) ON DELETE CASCADE;

--
-- Filtros para la tabla `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`Id_Traveler`) REFERENCES `travelers` (`Id_Traveler`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`Id_User`) REFERENCES `system_users` (`Id_User`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`Id_Sender_User`) REFERENCES `system_users` (`Id_User`) ON DELETE SET NULL;

--
-- Filtros para la tabla `passenger`
--
ALTER TABLE `passenger`
  ADD CONSTRAINT `passenger_ibfk_1` FOREIGN KEY (`Id_Booking`) REFERENCES `bookings` (`Id_Booking`),
  ADD CONSTRAINT `passenger_ibfk_2` FOREIGN KEY (`Id_Traveler`) REFERENCES `travelers` (`Id_Traveler`),
  ADD CONSTRAINT `passenger_ibfk_3` FOREIGN KEY (`Id_Trip`) REFERENCES `trips` (`Id_Trip`);

--
-- Filtros para la tabla `system_users`
--
ALTER TABLE `system_users`
  ADD CONSTRAINT `system_users_ibfk_1` FOREIGN KEY (`Id_Traveler`) REFERENCES `travelers` (`Id_Traveler`) ON DELETE CASCADE;

--
-- Filtros para la tabla `trip_reviews`
--
ALTER TABLE `trip_reviews`
  ADD CONSTRAINT `trip_reviews_ibfk_1` FOREIGN KEY (`Id_Trip`) REFERENCES `trips` (`Id_Trip`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_reviews_ibfk_2` FOREIGN KEY (`Id_User`) REFERENCES `system_users` (`Id_User`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

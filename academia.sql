-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-03-2024 a las 23:15:13
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `academia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_tareas`
--

CREATE TABLE `asignaciones_tareas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignaciones_tareas`
--

INSERT INTO `asignaciones_tareas` (`id`, `tarea_id`, `user_id`) VALUES
(5, 12, 19),
(6, 12, 20),
(8, 13, 19),
(9, 13, 20),
(10, 14, 19),
(11, 14, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `presentes` varchar(1000) DEFAULT NULL,
  `ausentes` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asistencia`
--

INSERT INTO `asistencia` (`id`, `materia_id`, `curso_id`, `fecha`, `hora`, `presentes`, `ausentes`) VALUES
(24, 17, 3, '2024-03-17', '21:30:57', '19', '18,20'),
(25, 21, 5, '2024-03-17', '21:31:01', '18', ''),
(26, 18, 3, '2024-03-17', '21:34:42', '19', '20'),
(27, 19, 4, '2024-03-17', '21:34:55', '18', ''),
(28, 17, 3, '2024-03-19', '01:14:13', '18', '19,20'),
(29, 21, 5, '2024-03-19', '01:14:22', '18', ''),
(30, 17, 3, '2024-03-17', '21:30:57', '19', '18,20'),
(31, 21, 5, '2024-03-17', '21:31:01', '18', ''),
(32, 18, 3, '2024-03-17', '21:34:42', '19', '20'),
(33, 19, 4, '2024-03-17', '21:34:55', '18', ''),
(34, 17, 3, '2024-03-19', '01:14:13', '18', '19,20'),
(35, 21, 5, '2024-03-19', '01:14:22', '18', ''),
(36, 17, 3, '2024-03-17', '21:30:57', '19', '18,20'),
(37, 21, 5, '2024-03-17', '21:31:01', '18', ''),
(38, 18, 3, '2024-03-17', '21:34:42', '19', '20'),
(39, 19, 4, '2024-03-17', '21:34:55', '18', ''),
(40, 17, 3, '2024-03-19', '01:14:13', '18', '19,20'),
(41, 21, 5, '2024-03-19', '01:14:22', '18', ''),
(42, 17, 3, '2024-03-17', '21:30:57', '19', '18,20'),
(43, 21, 5, '2024-03-17', '21:31:01', '18', ''),
(44, 18, 3, '2024-03-17', '21:34:42', '19', '20'),
(45, 19, 4, '2024-03-17', '21:34:55', '18', ''),
(46, 17, 3, '2024-03-19', '01:14:13', '18', '19,20'),
(47, 21, 5, '2024-03-19', '01:14:22', '18', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `begin` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration` int(3) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `title`, `description`, `begin`, `duration`, `date`) VALUES
(3, 'Turno Mañana', 'Desde 8 am hasta las 13 pm', '2024-03-18 03:00:00', 54, '2024-03-17 16:02:34'),
(4, 'Turno Tarde', 'Desde 13 pm hasta 7 pm', '2024-03-18 03:00:00', 54, '2024-03-17 16:03:02'),
(5, 'Turno Noche', 'Desde 7pm a 23 pm', '2024-03-18 03:00:00', 54, '2024-03-17 19:50:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_tareas`
--

CREATE TABLE `entregas_tareas` (
  `id` int(11) NOT NULL,
  `tarea_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `fecha_entrega` timestamp NOT NULL DEFAULT current_timestamp(),
  `archivo_entregado` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entregas_tareas`
--

INSERT INTO `entregas_tareas` (`id`, `tarea_id`, `user_id`, `mensaje`, `fecha_entrega`, `archivo_entregado`) VALUES
(2, 12, 19, 'Funciona', '2024-03-21 23:38:01', 0x433a2f78616d70702f6874646f63732f61636164656d69612f75706c6f6164732f7461726561732f363566636334643961356431315f43726f6e6f6772c3a16d612e786c7378),
(3, 12, 19, 'Excelente', '2024-03-21 23:39:08', 0x433a2f78616d70702f6874646f63732f61636164656d69612f75706c6f6164732f7461726561732f363566636335316330316533355f696d7072696d655f696e736372697063696f6e2e706466);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones`
--

CREATE TABLE `evaluaciones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `hora_inicio` time NOT NULL DEFAULT current_timestamp(),
  `hora_fin` time NOT NULL DEFAULT current_timestamp(),
  `intentos_permitidos` int(11) NOT NULL DEFAULT 1,
  `ponderacion` float DEFAULT NULL,
  `duracion_estimada` time DEFAULT NULL,
  `tipo_evaluacion` enum('examen','cuestionario','proyecto','otro') DEFAULT NULL,
  `estado` enum('activo','inactivo','programado') DEFAULT 'activo',
  `instrucciones` text DEFAULT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `user_id`, `course_id`, `date`) VALUES
(59, 18, 4, '2024-03-17 19:45:05'),
(60, 19, 3, '2024-03-17 19:45:20'),
(61, 20, 3, '2024-03-17 19:47:44'),
(62, 18, 5, '2024-03-17 19:51:23');

--
-- Disparadores `inscripciones`
--
DELIMITER $$
CREATE TRIGGER `after_inscripcion_insert` AFTER INSERT ON `inscripciones` FOR EACH ROW BEGIN
    -- Insertar en inscripciones_materias para cada materia asociada al curso
    INSERT INTO inscripciones_materias (user_id, materia_id, fecha_inscripcion)
    SELECT NEW.user_id, materias.id, NOW()
    FROM materias
    WHERE materias.curso_id = NEW.course_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones_materias`
--

CREATE TABLE `inscripciones_materias` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `fecha_inscripcion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones_materias`
--

INSERT INTO `inscripciones_materias` (`id`, `user_id`, `materia_id`, `fecha_inscripcion`) VALUES
(32, 18, 19, '2024-03-17 19:45:05'),
(33, 18, 17, '2024-03-17 19:45:05'),
(35, 19, 17, '2024-03-17 19:45:20'),
(36, 19, 18, '2024-03-17 19:45:20'),
(38, 20, 17, '2024-03-17 19:47:44'),
(39, 20, 18, '2024-03-17 19:47:44'),
(41, 18, 21, '2024-03-17 19:51:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instructores`
--

CREATE TABLE `instructores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `dni` bigint(20) NOT NULL,
  `especialidad` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `instructores`
--

INSERT INTO `instructores` (`id`, `nombre`, `apellido`, `email`, `dni`, `especialidad`) VALUES
(16, 'Ramiro', 'Belfiore', 'ramirobelfiore@gmail.com', 46696013, 'Ingeniería'),
(17, 'Nacho', 'Torrente', 'nacho@gmail.com', 45696013, 'Inglés');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo_lectura` enum('obligatoria','complementaria') NOT NULL,
  `fecha` date NOT NULL DEFAULT current_timestamp(),
  `tamano` float DEFAULT NULL,
  `materia_id` int(11) DEFAULT NULL,
  `archivo` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id`, `nombre_archivo`, `tipo_lectura`, `fecha`, `tamano`, `materia_id`, `archivo`) VALUES
(8, 'Funcionalidades', 'obligatoria', '2024-03-17', 16254, 17, 0x433a2f78616d70702f6874646f63732f61636164656d69612f75706c6f6164732f6d6174657269616c65732f363566373633616363623562385f46756e63696f6e616c6964616465732e646f6378),
(9, 'Workbook', 'complementaria', '2024-03-17', 18092, 18, 0x433a2f78616d70702f6874646f63732f61636164656d69612f75706c6f6164732f6d6174657269616c65732f363566373731333339336161315f696d7072696d655f696e736372697063696f6e2e706466);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `instructor` varchar(255) NOT NULL,
  `curso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `instructor`, `curso_id`) VALUES
(17, 'Matemática I', '16', 3),
(18, 'Inglés I', '17', 3),
(19, 'Danza', '17', 4),
(21, 'Prácticas del Lenguaje', '16', 5),
(22, 'Música', '17', 3),
(23, 'Ciencias', '16', 5),
(24, 'Física', '17', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int(11) NOT NULL,
  `evaluacion_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nota` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `evaluacion_id` int(11) DEFAULT NULL,
  `pregunta` text DEFAULT NULL,
  `tipo_pregunta` enum('opcion_multiple','respuesta_corta','carga_archivos','otro') DEFAULT NULL,
  `opciones` varchar(1500) NOT NULL,
  `respuesta_correcta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_alumnos`
--

CREATE TABLE `respuestas_alumnos` (
  `id` int(11) NOT NULL,
  `evaluacion_id` int(11) DEFAULT NULL,
  `pregunta_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `archivo_subido` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha_entrega` date NOT NULL,
  `archivo` longblob NOT NULL,
  `fecha_emision` timestamp NOT NULL DEFAULT current_timestamp(),
  `materia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `nombre`, `descripcion`, `fecha_entrega`, `archivo`, `fecha_emision`, `materia_id`) VALUES
(12, 'Resolver Ecuaciones', 'Seguir las instrucciones', '2024-03-19', '', '2024-03-17 21:26:08', 17),
(13, 'Grammar', 'Resolver', '2024-03-26', '', '2024-03-17 21:27:50', 18),
(14, 'Maths', 'Funciona', '2024-03-27', 0x433a2f78616d70702f6874646f63732f61636164656d69612f75706c6f6164732f7461726561732f363566636362353830386639355f46756e63696f6e616c6964616465732e646f6378, '2024-03-22 00:05:44', 17);

--
-- Disparadores `tareas`
--
DELIMITER $$
CREATE TRIGGER `after_tarea_insert` AFTER INSERT ON `tareas` FOR EACH ROW BEGIN
    -- Insertar en asignaciones_tareas para cada usuario inscrito en la materia de la tarea
    INSERT INTO asignaciones_tareas (tarea_id, user_id)
    SELECT NEW.id, inscripciones.user_id
    FROM inscripciones
    INNER JOIN materias ON materias.id = NEW.materia_id
    WHERE materias.curso_id = inscripciones.course_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(1) NOT NULL,
  `token` varchar(255) NOT NULL,
  `twoFactor` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `name`, `surname`, `gender`, `email`, `password`, `role`, `token`, `twoFactor`, `active`, `date`) VALUES
(16, 'Ramiro', 'Belfiore', 'male', 'ramirobelfiore@gmail.com', '$2y$10$SLqeHTgNl0K9/Qaeo093u.sCYJuQ74upXGRh6J6XYyIZdZGaM6s62', 1, '', 1, 1, '2024-03-17 12:50:08'),
(17, 'Nacho', 'Torrente', 'male', 'nacho@gmail.com', '$2y$10$SLqeHTgNl0K9/Qaeo093u.sCYJuQ74upXGRh6J6XYyIZdZGaM6s62', 0, '', 0, 1, '2024-03-17 12:51:27'),
(18, 'Santiago', 'Belfiore', 'male', 'santi@gmail.com', '$2y$10$jdrMjjKmfGVB4ibrE.VucOJG6.ap285cO6w6mK/rZ63gG2hSL..n.', 0, '', 0, 1, '2024-03-17 12:51:40'),
(19, 'Alejandro', 'Puche', 'male', 'alenochi@gmail.com', '$2y$10$1dvmjhDp0DdAXG2LPfvYG.2d1uGFDTyVa/8yRNrN6COhvrmIHQS0W', 0, '', 0, 1, '2024-03-17 12:51:58'),
(20, 'Mónica', 'Tornatore', 'female', 'monica@gmail.com', '$2y$10$OqR3NnpJn7gAeYj.hUABQODSqvXg5EnpwQdWI.MULlLF/6z8fm0T.', 0, '', 0, 1, '2024-03-17 12:52:21'),
(21, 'Malena', 'Angel', 'ratherNotSay', 'male@gmail.com', '$2y$10$rsyBKOv8W8ccOLdVWo2i..lbwTrJfw8rmYTN05DpcAaJtofPhrqN2', 0, '', 0, 1, '2024-03-18 22:03:06');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignaciones_tareas`
--
ALTER TABLE `asignaciones_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materia_id` (`materia_id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indices de la tabla `inscripciones_materias`
--
ALTER TABLE `inscripciones_materias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indices de la tabla `instructores`
--
ALTER TABLE `instructores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`);

--
-- Indices de la tabla `respuestas_alumnos`
--
ALTER TABLE `respuestas_alumnos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`),
  ADD KEY `pregunta_id` (`pregunta_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materia_id` (`materia_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones_tareas`
--
ALTER TABLE `asignaciones_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `inscripciones_materias`
--
ALTER TABLE `inscripciones_materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `instructores`
--
ALTER TABLE `instructores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `respuestas_alumnos`
--
ALTER TABLE `respuestas_alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones_tareas`
--
ALTER TABLE `asignaciones_tareas`
  ADD CONSTRAINT `asignaciones_tareas_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`),
  ADD CONSTRAINT `asignaciones_tareas_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`),
  ADD CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  ADD CONSTRAINT `entregas_tareas_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id`),
  ADD CONSTRAINT `entregas_tareas_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD CONSTRAINT `evaluaciones_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`);

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `course_id` FOREIGN KEY (`course_id`) REFERENCES `cursos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `inscripciones_materias`
--
ALTER TABLE `inscripciones_materias`
  ADD CONSTRAINT `inscripciones_materias_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `inscripciones_materias_ibfk_2` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`);

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `materiales_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`);

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`);

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`),
  ADD CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`);

--
-- Filtros para la tabla `respuestas_alumnos`
--
ALTER TABLE `respuestas_alumnos`
  ADD CONSTRAINT `respuestas_alumnos_ibfk_1` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`),
  ADD CONSTRAINT `respuestas_alumnos_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `preguntas` (`id`),
  ADD CONSTRAINT `respuestas_alumnos_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-04-2026 a las 15:16:50
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
-- Base de datos: `sportmanager`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `limpiar_logs` (IN `dias` INT)   BEGIN
    
    DELETE FROM logs
    WHERE fecha < NOW() - INTERVAL dias DAY;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id_archivo` int(11) NOT NULL,
  `id_deportista` int(11) NOT NULL,
  `descripcion_arch` varchar(255) NOT NULL,
  `archivo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id_asistencia` int(10) UNSIGNED NOT NULL,
  `id_deportista` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL,
  `estado` varchar(20) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
PARTITION BY RANGE (to_days(`fecha`))
(
PARTITION p2026_q1 VALUES LESS THAN (740072) ENGINE=InnoDB,
PARTITION p2026_q2 VALUES LESS THAN (740163) ENGINE=InnoDB,
PARTITION p2026_q3 VALUES LESS THAN (740255) ENGINE=InnoDB,
PARTITION p2026_q4 VALUES LESS THAN (740347) ENGINE=InnoDB,
PARTITION p2027_q1 VALUES LESS THAN (740437) ENGINE=InnoDB,
PARTITION p2027_q2 VALUES LESS THAN (740528) ENGINE=InnoDB,
PARTITION p2027_q3 VALUES LESS THAN (740620) ENGINE=InnoDB,
PARTITION p2027_q4 VALUES LESS THAN (740712) ENGINE=InnoDB,
PARTITION p_future VALUES LESS THAN MAXVALUE ENGINE=InnoDB
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre_cat` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre_cat`) VALUES
(1, 'sub-7'),
(2, 'sub-9'),
(3, 'sub-11'),
(4, 'sub-13'),
(5, 'sub-15'),
(6, 'sub-17'),
(7, 'sub-19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_descuento`
--

CREATE TABLE `codigos_descuento` (
  `id_codigo` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `descuento` decimal(10,2) NOT NULL,
  `tipo_descuento` enum('porcentaje','monto_fijo') NOT NULL,
  `cantidad_usos` int(11) NOT NULL DEFAULT 1,
  `id_metodo_pago` int(11) NOT NULL,
  `fecha_expiracion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deportistas`
--

CREATE TABLE `deportistas` (
  `id_deportista` int(11) NOT NULL,
  `tipo_documento` varchar(5) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `nombres` varchar(30) DEFAULT NULL,
  `apellidos` varchar(30) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `jornada` varchar(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_categoria` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `genero` varchar(10) DEFAULT NULL,
  `id_nivel` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `deportistas`
--

INSERT INTO `deportistas` (`id_deportista`, `tipo_documento`, `foto`, `nombres`, `apellidos`, `fecha_nacimiento`, `jornada`, `fecha_registro`, `id_categoria`, `id_usuario`, `id_estado`, `genero`, `id_nivel`) VALUES
(1020304050, 'TI', '1774389537.png', 'Pedro', 'Landa', '2017-06-08', 'Mañana', '2026-03-24 21:58:57', 1, 987456321, 1, 'Masculino', 2),
(1050406020, 'TI', '1773933481_bc2b2d21734903ebf1319b7a5e5aa6e6.jpg', 'yuli', 'ruiz', '2016-07-05', 'Mañana', '2026-03-19 15:18:01', 3, 321456987, 3, 'Femenino', 0),
(2147483647, 'TI', '1774450332_320.webp', 'Lola', 'Landa', '2014-06-10', 'Tarde', '2026-03-24 23:48:42', 3, 987456321, 2, 'Femenino', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escuelas`
--

CREATE TABLE `escuelas` (
  `id_escuela` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `disciplina` varchar(20) NOT NULL,
  `dia_pago` tinyint(2) NOT NULL,
  `valor_inscripcion` decimal(10,2) NOT NULL,
  `valor_mensualidad` decimal(10,2) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `pass_app` varchar(60) NOT NULL,
  `telefono` varchar(11) NOT NULL,
  `direccion` varchar(50) NOT NULL,
  `escudo_path` varchar(255) DEFAULT NULL,
  `firma_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `escuelas`
--

INSERT INTO `escuelas` (`id_escuela`, `nombre`, `disciplina`, `dia_pago`, `valor_inscripcion`, `valor_mensualidad`, `correo`, `pass_app`, `telefono`, `direccion`, `escudo_path`, `firma_path`) VALUES
(1, 'Distrito SportingFC', 'Fútbol', 30, 45000.00, 40000.00, 'ejemplo@gmail.com', '111111111111', '3111111111', 'ejemplo #1', 'bd268acc0f589035_1767655463.png', '4e186525019f32c4_1767655463.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id_estado` int(11) NOT NULL,
  `nombre_estado` enum('activo','suspendido','retirado') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id_estado`, `nombre_estado`) VALUES
(1, 'activo'),
(2, 'suspendido'),
(3, 'retirado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL,
  `titulo` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `tipo_evento` varchar(50) NOT NULL DEFAULT 'mensualidad',
  `costo` decimal(15,2) DEFAULT NULL,
  `cuotas` tinyint(2) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id_evento`, `titulo`, `fecha`, `id_rol`, `tipo_evento`, `costo`, `cuotas`, `estado`) VALUES
(0, 'Copa', '2026-03-28', 1, 'Torneo', 40000.00, 2, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id_factura` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL,
  `fecha_emision` datetime NOT NULL,
  `tipo_pago` int(11) NOT NULL,
  `id_deportista` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `descripcion` varchar(100) DEFAULT 'N/A',
  `id_evento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id_inscripcion` int(11) NOT NULL,
  `id_evento` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_deportista` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_evento`, `id_usuario`, `id_deportista`, `fecha`) VALUES
(3, 0, 987456321, 1020304050, '2026-03-27 20:47:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones_detalle`
--

CREATE TABLE `inscripciones_detalle` (
  `id_pago` int(11) NOT NULL,
  `cantidad_deportistas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `id_log` int(11) NOT NULL,
  `tabla` varchar(100) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `registro_id` varchar(255) NOT NULL,
  `valores_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_anteriores`)),
  `valores_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`valores_nuevos`)),
  `usuario` varchar(255) DEFAULT current_user(),
  `ip_host` varchar(255) DEFAULT substring_index(user(),'@',-1),
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id_metodo` int(11) NOT NULL,
  `id_escuela` int(11) NOT NULL,
  `nombre_entidad` varchar(50) NOT NULL,
  `qr_path` varchar(255) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT 'offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel`
--

CREATE TABLE `nivel` (
  `id_nivel` int(11) NOT NULL,
  `nombre` varchar(10) NOT NULL,
  `grupo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel`
--

INSERT INTO `nivel` (`id_nivel`, `nombre`, `grupo`) VALUES
(0, 'Sedentario', '1'),
(1, 'Activo', '2'),
(2, 'Entrenado', '3'),
(3, 'nacional', '4'),
(4, ' Élite', '5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_deportista` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `estado_pago` enum('pendiente','pagado') NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id` int(11) DEFAULT NULL,
  `tipo_pago` varchar(50) NOT NULL DEFAULT 'mensualidad',
  `id_evento` int(11) DEFAULT NULL,
  `registrado_por` varchar(50) DEFAULT NULL,
  `id_codigo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `cantidad` tinyint(2) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `fecha_pedido` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(50) NOT NULL DEFAULT 'en espera',
  `abono` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'acudiente'),
(2, 'formador'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguridad`
--

CREATE TABLE `seguridad` (
  `id_seguridad` int(11) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `id` int(11) DEFAULT NULL,
  `ip` varchar(30) DEFAULT NULL,
  `intentos` int(11) DEFAULT 0,
  `bloqueo_hasta` datetime DEFAULT NULL,
  `ultimo_intento` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id_sesion` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `tipo_archivo` enum('pdf','imagen') NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_sesion` date NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `comentario` text DEFAULT NULL,
  `fecha_eliminacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci
PARTITION BY RANGE (to_days(`fecha_sesion`))
(
PARTITION p2026_q1 VALUES LESS THAN (740072) ENGINE=InnoDB,
PARTITION p2026_q2 VALUES LESS THAN (740163) ENGINE=InnoDB,
PARTITION p2026_q3 VALUES LESS THAN (740255) ENGINE=InnoDB,
PARTITION p2026_q4 VALUES LESS THAN (740347) ENGINE=InnoDB,
PARTITION p2027_q1 VALUES LESS THAN (740437) ENGINE=InnoDB,
PARTITION p2027_q2 VALUES LESS THAN (740528) ENGINE=InnoDB,
PARTITION p2027_q3 VALUES LESS THAN (740620) ENGINE=InnoDB,
PARTITION p2027_q4 VALUES LESS THAN (740712) ENGINE=InnoDB,
PARTITION p_future VALUES LESS THAN MAXVALUE ENGINE=InnoDB
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uniformes`
--

CREATE TABLE `uniformes` (
  `id_uniforme` int(11) NOT NULL,
  `numero_camiseta` int(11) NOT NULL,
  `id_deportista` int(11) NOT NULL,
  `tipo_uniforme` enum('competencia','entrenamiento','extra') NOT NULL,
  `nombre_camiseta` varchar(10) NOT NULL,
  `descripcion_uniforme` text DEFAULT 'N/A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `tipo_documento` varchar(50) NOT NULL,
  `id_escuela` int(11) DEFAULT NULL,
  `nombres` varchar(30) NOT NULL,
  `apellidos` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contrasena` varchar(60) NOT NULL,
  `telefono` varchar(11) NOT NULL,
  `mfa` tinyint(1) NOT NULL DEFAULT 1,
  `id_rol` int(11) NOT NULL,
  `registros_disponibles` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL,
  `habilitado` tinyint(1) NOT NULL DEFAULT 0,
  `inicios_sesion` int(11) DEFAULT 0,
  `estado` varchar(20) DEFAULT 'aprobado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `tipo_documento`, `id_escuela`, `nombres`, `apellidos`, `email`, `contrasena`, `telefono`, `mfa`, `id_rol`, `registros_disponibles`, `reset_token`, `token_expiry`, `habilitado`, `inicios_sesion`, `estado`) VALUES
(22222222, 'CC', 1, 'demo', 'demo', 'demo123@gmail.com', '$2y$12$xmX0gfB5v/0OtyrYuP90D.Vzmzmbml2f/SmqKq5s/CkCwNc1O0/1S', '30000000000', 1, 3, NULL, NULL, NULL, 1, 0, 'aprobado'),
(321456987, 'CC', 1, 'yuli', 'moris', 'yuli123@gmail.com', '$2y$12$MqjHkRTJY6Cad65L/yzdSOkaU3TDeGXG2mNV9N/tKBBkeaui7j7ky', '5451362587', 1, 1, NULL, NULL, NULL, 1, 0, 'aprobado'),
(987456321, 'CE', 1, 'lalo', 'landa', 'lalo123@gmail.com', '$2y$12$B1QMliVNkmIRjNktyeC7ieudJjGq.ZxvyOvQ1w/3U/UWupVcPJNTu', '3654258941', 1, 1, NULL, NULL, NULL, 1, 0, 'aprobado'),
(1010200521, 'CC', 1, 'Daniel', 'Celis', 'dan.3072@hotmail.com', '$2y$10$CAR2J8pqyv8rcqcmDE8qjOQAVnFjZoW2mJH1IS2sLhdPQ5jRIB90K', '3157257392', 1, 3, NULL, NULL, NULL, 0, 0, 'pendiente'),
(1111111111, 'CC', 1, 'ejemplo', '', 'ejemplo@gmail.com', '$2y$10$PwuEFIlcRkP/r4Uc1mHVoObxnOdH4JrfBWqjfOxNxerqKgm6nLS5e', '3111111111', 0, 2, 1, NULL, NULL, 1, 1, 'aprobado');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id_archivo`),
  ADD KEY `archivos_ibfk_1` (`id_deportista`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id_asistencia`,`fecha`),
  ADD KEY `idx_deportista_fecha` (`id_deportista`,`fecha`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `codigos_descuento`
--
ALTER TABLE `codigos_descuento`
  ADD PRIMARY KEY (`id_codigo`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_metodo_pago` (`id_metodo_pago`);

--
-- Indices de la tabla `deportistas`
--
ALTER TABLE `deportistas`
  ADD PRIMARY KEY (`id_deportista`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `deportistas_ibfk_2` (`id_usuario`),
  ADD KEY `fk_deportistas_estados` (`id_estado`),
  ADD KEY `id_nivel` (`id_nivel`);

--
-- Indices de la tabla `escuelas`
--
ALTER TABLE `escuelas`
  ADD PRIMARY KEY (`id_escuela`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `fk_id_rol_eventos` (`id_rol`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `id` (`id`),
  ADD KEY `id_deportista` (`id_deportista`),
  ADD KEY `id_evento` (`id_evento`),
  ADD KEY `tipo_pago` (`tipo_pago`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD KEY `id_evento` (`id_evento`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_deportista` (`id_deportista`);

--
-- Indices de la tabla `inscripciones_detalle`
--
ALTER TABLE `inscripciones_detalle`
  ADD PRIMARY KEY (`id_pago`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id_log`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id_metodo`),
  ADD KEY `id_escuela` (`id_escuela`);

--
-- Indices de la tabla `nivel`
--
ALTER TABLE `nivel`
  ADD PRIMARY KEY (`id_nivel`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_deportista` (`id_deportista`),
  ADD KEY `id_evento` (`id_evento`),
  ADD KEY `id_codigo` (`id_codigo`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_factura` (`id_factura`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `seguridad`
--
ALTER TABLE `seguridad`
  ADD PRIMARY KEY (`id_seguridad`);

--
-- Indices de la tabla `uniformes`
--
ALTER TABLE `uniformes`
  ADD PRIMARY KEY (`id_uniforme`),
  ADD KEY `id_deportista` (`id_deportista`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_escuela` (`id_escuela`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `fk_deportista` FOREIGN KEY (`id_deportista`) REFERENCES `deportistas` (`id_deportista`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`),
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD CONSTRAINT `metodos_pago_ibfk_1` FOREIGN KEY (`id_escuela`) REFERENCES `escuelas` (`id_escuela`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_deportista`) REFERENCES `deportistas` (`id_deportista`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`id_codigo`) REFERENCES `codigos_descuento` (`id_codigo`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_factura`) REFERENCES `facturas` (`id_factura`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `uniformes`
--
ALTER TABLE `uniformes`
  ADD CONSTRAINT `uniformes_ibfk_1` FOREIGN KEY (`id_deportista`) REFERENCES `deportistas` (`id_deportista`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_escuela`) REFERENCES `escuelas` (`id_escuela`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

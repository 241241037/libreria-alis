-- Librería ALIS - Esquema de base de datos
-- Importar en phpMyAdmin (XAMPP) o vía consola mysql

CREATE DATABASE IF NOT EXISTS libreria_alis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE libreria_alis;

-- ---------------------------------------------------------
-- USUARIO
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- LIBRO  (incluye imagen_url solicitada)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS libro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) NOT NULL DEFAULT '',
    genero VARCHAR(100) NOT NULL DEFAULT '',
    isbn VARCHAR(20) NOT NULL UNIQUE,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    imagen_url VARCHAR(500) DEFAULT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- RESERVA  (apartado de 48 horas)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS reserva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME NOT NULL,
    estado ENUM('RESERVADO_PENDIENTE','LIQUIDADA','CANCELADA','EXPIRADA') NOT NULL DEFAULT 'RESERVADO_PENDIENTE',
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- DETALLE_RESERVA
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS detalle_reserva (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    libro_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    FOREIGN KEY (reserva_id) REFERENCES reserva(id) ON DELETE CASCADE,
    FOREIGN KEY (libro_id) REFERENCES libro(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- NOTA: el usuario administrador NO se crea aquí.
-- Después de importar este script, abre setup_admin.php en el navegador
-- UNA SOLA VEZ para crear el primer administrador de forma segura
-- (usa password_hash() de PHP, no un hash escrito a mano).
-- Por seguridad, borra o renombra setup_admin.php cuando termines.
-- ---------------------------------------------------------

-- ---------------------------------------------------------
-- Datos de ejemplo de libros
-- ---------------------------------------------------------
INSERT INTO libro (titulo, autor, genero, isbn, precio, stock, imagen_url) VALUES
('Cien años de soledad', 'Gabriel García Márquez', 'Realismo mágico', '9780307474728', 289.00, 12, 'https://images-na.ssl-images-amazon.com/images/I/91TvVQS7loL.jpg'),
('1984', 'George Orwell', 'Distopía', '9780451524935', 199.00, 8, 'https://images-na.ssl-images-amazon.com/images/I/71kxa1-0mfL.jpg'),
('El Principito', 'Antoine de Saint-Exupéry', 'Fábula', '9780156012195', 149.00, 20, 'https://images-na.ssl-images-amazon.com/images/I/71OZY035QKL.jpg'),
('Rayuela', 'Julio Cortázar', 'Novela experimental', '9788437604572', 259.00, 5, NULL)
ON DUPLICATE KEY UPDATE isbn = isbn;

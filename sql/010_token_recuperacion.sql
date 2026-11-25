-- Recuperación de contraseña: cada fila es un enlace enviado por correo
-- En token se guarda el hash SHA-256 del token del enlace, no el token en sí:
-- si alguien lee la base de datos no puede usar los enlaces
-- Al usar el enlace o pedir uno nuevo se borran los tokens del usuario
SET NAMES utf8mb4;

CREATE TABLE Token_Recuperacion (
    id_token INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    token CHAR(64) NOT NULL,
    fecha_expiracion DATETIME NOT NULL,

    CONSTRAINT PK_Token_Recuperacion
        PRIMARY KEY (id_token),

    CONSTRAINT UQ_Token_Recuperacion_Token
        UNIQUE (token),

    -- Si se borra el usuario se borran sus tokens
    CONSTRAINT FK_Token_Recuperacion_Usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

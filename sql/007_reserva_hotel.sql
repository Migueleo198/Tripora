-- Reservas de hotel sin viaje: un usuario contrata noches en un hotel con precio_noche
-- precio_total se guarda al reservar (noches * precio_noche de ese momento)
SET NAMES utf8mb4;

CREATE TABLE Reserva_Hotel (
    id_reserva_hotel INT AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_hotel INT NOT NULL,
    estado ENUM('PENDIENTE', 'CONFIRMADA', 'CANCELADA') NOT NULL,
    fecha_reserva DATETIME NOT NULL,
    fecha_entrada DATE NOT NULL,
    noches TINYINT UNSIGNED NOT NULL,
    precio_total DECIMAL(10,2) NOT NULL,

    CONSTRAINT PK_Reserva_Hotel
        PRIMARY KEY (id_reserva_hotel),

    CONSTRAINT FK_Reserva_Hotel_Usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT FK_Reserva_Hotel_Hotel
        FOREIGN KEY (id_hotel)
        REFERENCES Hotel(id_hotel)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT CK_Reserva_Hotel_Noches
        CHECK (noches >= 1),

    CONSTRAINT CK_Reserva_Hotel_Precio
        CHECK (precio_total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

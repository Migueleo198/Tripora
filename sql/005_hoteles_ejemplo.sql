-- Hoteles de ejemplo, uno por destino de los viajes existentes (dos en los Alpes)
SET NAMES utf8mb4;

INSERT INTO Hotel (nombre, direccion, ciudad, estrellas) VALUES
    ('Hotel Rive Gauche', 'Rue de Rivoli 12', 'París', 4),
    ('Hotel Trastevere', 'Via della Lungaretta 45', 'Roma', 3),
    ('Hotel Costa Adeje', 'Avenida de España 8', 'Costa Adeje', 4),
    ('Hotel Shinjuku Garden', '3-2-1 Nishi-Shinjuku', 'Tokio', 4),
    ('Hotel Albaicín', 'Calle Elvira 20', 'Granada', 3),
    ('Chalet Matterhorn', 'Bahnhofstrasse 5', 'Zermatt', 5),
    ('Hostal Jungfrau', 'Höheweg 30', 'Interlaken', NULL);

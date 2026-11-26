#!/bin/sh
# Arranque del contenedor único: crea la BD desde el volcado y luego lanza Apache
set -e

VOLCADO=/etc/secrets/tripora.sql

# Contraseña interna de la BD (solo se usa dentro del contenedor)
if [ -z "$DB_PASSWORD" ]; then
    DB_PASSWORD=$(head -c 18 /dev/urandom | base64 | tr -dc 'A-Za-z0-9')
    export DB_PASSWORD
fi

# BD nueva en cada arranque (con lower_case_table_names=1 desde el principio)
rm -rf /var/lib/mysql/*
mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null
mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
mysqld_safe --user=mysql > /dev/null 2>&1 &

for i in $(seq 1 60); do
    mysqladmin ping --silent 2>/dev/null && break
    sleep 1
done

mysql -uroot <<SQL
CREATE DATABASE IF NOT EXISTS tripora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'tripora'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
CREATE USER IF NOT EXISTS 'tripora'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL ON tripora.* TO 'tripora'@'localhost';
GRANT ALL ON tripora.* TO 'tripora'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

if [ -f "$VOLCADO" ]; then
    mysql -uroot tripora < "$VOLCADO"
    echo "Tripora: datos cargados desde $VOLCADO"
else
    echo "Tripora: AVISO, no hay volcado en $VOLCADO (BD vacía)"
fi

# Render indica el puerto en $PORT
PUERTO=${PORT:-80}
sed -i "s/^Listen .*/Listen $PUERTO/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:$PUERTO>/" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground

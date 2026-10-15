-- Inicialización de la base de datos festival_acb
-- El usuario acb_app y la BD ya son creados por la variable MYSQL_USER/MYSQL_DATABASE.
-- Aquí se refuerzan los privilegios y el utilizar un socket seguro para 127.0.0.1.

CREATE DATABASE IF NOT EXISTS festival_acb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'acb_app'@'%' IDENTIFIED BY 'acb_local_2026';
ALTER USER IF EXISTS 'acb_app'@'%' IDENTIFIED BY 'acb_local_2026';
GRANT ALL PRIVILEGES ON festival_acb.* TO 'acb_app'@'%';

FLUSH PRIVILEGES;
-- create databases
CREATE DATABASE IF NOT EXISTS `service_db`;

-- create root user and grant rights
CREATE USER 'service_user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON service_db.* TO 'service_user'@'%';

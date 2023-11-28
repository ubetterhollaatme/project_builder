CREATE DATABASE IF NOT EXISTS `{{ db_name }}`;

CREATE USER '{{ db_user }}'@'%' IDENTIFIED BY '{{ db_pass }}';
GRANT ALL PRIVILEGES ON {{ db_name }}.* TO '{{ db_user }}'@'%';

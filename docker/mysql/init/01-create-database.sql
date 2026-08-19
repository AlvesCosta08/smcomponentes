-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS smcomponentes 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Criar usuário (opcional, se quiser um usuário específico)
-- CREATE USER IF NOT EXISTS 'smuser'@'%' IDENTIFIED BY 'password';
-- GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'%';
-- FLUSH PRIVILEGES;

-- Verificar criação
SELECT 'Database smcomponentes created successfully!' AS status;
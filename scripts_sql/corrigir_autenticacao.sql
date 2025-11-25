-- Tente executar estas opções uma por uma até funcionar.

-- OPÇÃO 1: Sintaxe Padrão MySQL 8.0
ALTER USER 'inventario'@'localhost' IDENTIFIED WITH mysql_native_password BY 'fA9-A@BLn_PiHsR0';

-- OPÇÃO 2: Sintaxe Simplificada (Pode funcionar se o servidor já estiver configurado corretamente)
-- ALTER USER 'inventario'@'localhost' IDENTIFIED BY 'fA9-A@BLn_PiHsR0';

-- OPÇÃO 3: Sintaxe Antiga / MariaDB
-- SET PASSWORD FOR 'inventario'@'localhost' = PASSWORD('fA9-A@BLn_PiHsR0');

-- OPÇÃO 4: Sintaxe MariaDB com Plugin
-- ALTER USER 'inventario'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('fA9-A@BLn_PiHsR0');

FLUSH PRIVILEGES;

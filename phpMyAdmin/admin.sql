CREATE USER admin@'localhost' IDENTIFIED BY 'admin';
GRANT ALL ON *.* TO admin@'localhost' IDENTIFIED BY 'admin' WITH GRANT OPTION;
FLUSH PRIVILEGES; 
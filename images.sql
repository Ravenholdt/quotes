CREATE TABLE IF NOT EXISTS images(
  id      INT AUTO_INCREMENT PRIMARY KEY,
  src     VARCHAR(300) NOT NULL UNIQUE,
  rating  INT NOT NULL DEFAULT '1000',
  matches INT NOT NULL DEFAULT '0'
);
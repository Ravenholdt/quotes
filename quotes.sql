DROP TABLE IF EXISTS quotes;

CREATE TABLE IF NOT EXISTS quotes(
  id          INT AUTO_INCREMENT PRIMARY KEY,
  quote       TEXT NOT NULL,
  context     VARCHAR(255) NOT NULL,
  rating      INT NOT NULL DEFAULT '1000',
  matches     INT NOT NULL DEFAULT '0',
  leftWon     INT NOT NULL DEFAULT '0',
  rightWon    INT NOT NULL DEFAULT '0',
  topSwipe    INT NOT NULL DEFAULT '0',
  bottomSwipe INT NOT NULL DEFAULT '0'
);

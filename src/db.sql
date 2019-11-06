CREATE TABLE IF NOT EXISTS sessions (
  SESSION_ID VARCHAR(32) UNIQUE NOT NULL,
  SERVER_ID VARCHAR(16) NOT NULL DEFAULT '123',
  SESSION_LASTACCESSTIME TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(SESSION_ID, SERVER_ID)
);

CREATE TABLE IF NOT EXISTS session_data (
  SESSION_ID VARCHAR(32) NOT NULL,
  SESSION_KEY VARCHAR(255) NOT NULL,
  SESSION_VALUE VARCHAR(255) NOT NULL,
  NESTED BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY(SESSION_ID, SESSION_KEY),
  FOREIGN KEY(SESSION_ID) REFERENCES sessions (SESSION_ID) ON DELETE CASCADE
);


-- ctrl+shift+enter --

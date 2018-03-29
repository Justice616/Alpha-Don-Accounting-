DROP DATABASE IF EXISTS `journal`;
CREATE DATABASE `journal`;

USE `journal`;

-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE user_types (
  id    INT          NOT NULL,
  title VARCHAR(191) NOT NULL UNIQUE,

  PRIMARY KEY (id)
);
INSERT INTO user_types VALUES (0, 'User');
INSERT INTO user_types VALUES (1, 'Manager');
INSERT INTO user_types VALUES (2, 'Administrator');

CREATE TABLE users (
  id       INT          NOT NULL AUTO_INCREMENT,
  name     VARCHAR(63)  NOT NULL UNIQUE,
  email    VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  type     INT          NOT NULL DEFAULT 0,

  created  DATETIME              DEFAULT CURRENT_TIMESTAMP,
  modified DATETIME              DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id)
);

INSERT INTO users
VALUES (1, 'user1', 'user1@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 0, now(), now());
INSERT INTO users
VALUES (2, 'user2', 'user2@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 0, now(), now());
INSERT INTO users
VALUES (3, 'manager1', 'manager1@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 1, now(),
        now());
INSERT INTO users
VALUES (4, 'manager2', 'manager2@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 1, now(),
        now());
INSERT INTO users
VALUES
  (5, 'admin1', 'admin1@email.com', '$2y$10$ka9qe5mHnmi6y2dl9GA53edHVpvXdkuVoqrYwld5Vbr8h0lWeqRde', 2, now(), now());

-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE account_categories (
  id   INT          NOT NULL,
  name VARCHAR(191) NOT NULL UNIQUE,

  PRIMARY KEY (id)
);

INSERT INTO account_categories VALUES (0, 'Assets');
INSERT INTO account_categories VALUES (1, 'Liabilities');
INSERT INTO account_categories VALUES (2, 'Owner''s equities');
INSERT INTO account_categories VALUES (3, 'Revenues');
INSERT INTO account_categories VALUES (4, 'Expenses');

CREATE TABLE account_subcategories (
  id   INT          NOT NULL,
  name VARCHAR(191) NOT NULL UNIQUE,

  PRIMARY KEY (id)
);

INSERT INTO account_subcategories VALUES (0, 'Short term');
INSERT INTO account_subcategories VALUES (1, 'Long term');
INSERT INTO account_subcategories VALUES (2, 'Current term');

CREATE TABLE accounts (
  id              INT          NOT NULL,
  name            VARCHAR(191) NOT NULL UNIQUE,
  category        INT          NOT NULL,
  subcategory     INT          NOT NULL,
  user_added      INT          NOT NULL,
  user_modified   INT                                      DEFAULT NULL,

  initial_balance FLOAT        NOT NULL                    DEFAULT 0,
  normal_side     CHAR         NOT NULL,

  status          INT          NOT NULL                    DEFAULT 0,

  created         DATETIME                                 DEFAULT CURRENT_TIMESTAMP,
  modified        DATETIME                                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (id),
  FOREIGN KEY (user_added) REFERENCES users (id),
  FOREIGN KEY (user_modified) REFERENCES users (id)
);

INSERT INTO accounts
(id, name, category, subcategory, user_added, initial_balance, normal_side)
VALUES (101, 'Cash', 0, 1, 5, 19000.500, 'D');
INSERT INTO accounts
(id, name, category, subcategory, user_added, initial_balance, normal_side)
VALUES (105, 'Petty Cash', 0, 0, 5, 20000.666, 'C');

-- ---------------------------------------------------------------------------------------------------------------------

CREATE TABLE journal_index (
  ref         INT      NOT NULL AUTO_INCREMENT,
  user        INT      NOT NULL,
  entry_date  DATETIME NOT NULL,
  description TEXT,
  files       TEXT,
  status      INT               DEFAULT 0,
  response    TEXT,

  created     DATETIME          DEFAULT CURRENT_TIMESTAMP,
  modified    DATETIME          DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (ref),
  FOREIGN KEY (user) REFERENCES users (id)
);

CREATE TABLE journal_entries (
  id          INT   NOT NULL AUTO_INCREMENT,

  journal_ref INT   NOT NULL,
  account_id  INT   NOT NULL,

  debit       FLOAT NOT NULL,
  credit      FLOAT NOT NULL,

  PRIMARY KEY (id)
);

-- ---------------------------------------------------------------------------------------------------------------------

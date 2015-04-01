CREATE TABLE IF NOT EXISTS wdq_constraints (
  ID                INT(11)       PRIMARY KEY AUTO_INCREMENT,
  pid               INT(11)       DEFAULT NULL,
  constraint_name   VARCHAR(255)  DEFAULT NULL,
  class             TEXT,
  constraint_status VARCHAR(255)  DEFAULT NULL,
  comment           TEXT,
  known_exception   TEXT,
  group_by          VARCHAR(255)  DEFAULT NULL,
  item              TEXT,
  list              TEXT,
  mandatory         VARCHAR(255)  DEFAULT NULL,
  maximum_date      VARCHAR(255)  DEFAULT NULL,
  maximum_quantity  VARCHAR(255)  DEFAULT NULL,
  minimum_date      VARCHAR(255)  DEFAULT NULL,
  minimum_quantity  VARCHAR(255)  DEFAULT NULL,
  namespace         VARCHAR(255)  DEFAULT NULL,
  pattern           VARCHAR(255)  DEFAULT NULL,
  property          VARCHAR(255)  DEFAULT NULL,
  relation          VARCHAR(255)  DEFAULT NULL,
  snak              VARCHAR(255)  DEFAULT NULL
);
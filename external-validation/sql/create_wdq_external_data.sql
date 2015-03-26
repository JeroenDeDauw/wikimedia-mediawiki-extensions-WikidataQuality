CREATE TABLE IF NOT EXISTS wdq_external_data (
  row_id            BIGINT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
  dump_id           INT UNSIGNED     NOT NULL,
  identifier_pid    INT UNSIGNED     NOT NULL,
  external_id       VARBINARY(100)   NOT NULL,
  pid               INT UNSIGNED     NOT NULL,
  external_value    VARBINARY(255)   NOT NULL
);

CREATE INDEX identifier_pid_external_id_pid ON wdq_external_data (identifier_pid, external_id, pid);

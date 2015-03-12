CREATE TABLE IF NOT EXISTS wdq_external_data (
  row_id            BIGINT UNSIGNED  NOT NULL PRIMARY KEY AUTO_INCREMENT,
  dump_id           BIGINT UNSIGNED  NOT NULL,
  identifier_pid    INT UNSIGNED     NOT NULL,
  external_id       VARBINARY(50)    NOT NULL,
  pid               INT UNSIGNED     NOT NULL,
  external_value    VARBINARY(255)   NOT NULL
);

CREATE INDEX external_data_id_pid_external_id_p_pid ON wdq_external_data (identifier_pid, external_id, pid);

CREATE TABLE IF NOT EXISTS wbq_external_data (
  row_id            BIGINT unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
  pid               INT unsigned    NOT NULL,
  entity_id         VARBINARY(50)   NOT NULL,
  entity_format     VARBINARY(20)   NOT NULL,
  entity_language   VARBINARY(20)   NOT NULL,
  entity_data       LONGBLOB        NOT NULL
);


CREATE INDEX external_data_pid_entity_id ON wbq_external_data (pid, entity_id);
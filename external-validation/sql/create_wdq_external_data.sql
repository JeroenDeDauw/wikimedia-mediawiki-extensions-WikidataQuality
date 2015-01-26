CREATE TABLE IF NOT EXISTS wdq_external_data (
  row_id            BIGINT UNSIGNED  NOT NULL PRIMARY KEY AUTO_INCREMENT,
  dump_id           BIGINT UNSIGNED  NOT NULL,
  pid               INT unsigned     NOT NULL,
  external_id       VARBINARY(50)    NOT NULL,
  external_data     LONGBLOB         NOT NULL
);

ALTER TABLE wdq_external_data ADD FOREIGN KEY(dump_id) REFERENCES wdq_dump_information(row_id);

CREATE INDEX external_data_pid_entity_id ON wbq_external_data (pid, entity_id);
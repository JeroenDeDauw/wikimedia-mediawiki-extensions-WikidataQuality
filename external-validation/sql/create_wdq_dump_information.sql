CREATE TABLE IF NOT EXISTS wdq_dump_information (
  row_id          BIGINT UNSIGNED  NOT NULL PRIMARY KEY AUTO_INCREMENT,
  source_item_id  INT(10)          NOT NULL,
  import_date     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  format          VARBINARY(30)    NOT NULL,
  language        VARBINARY(10)    NOT NULL,
  source_url      VARBINARY(300)   NOT NULL,
  size            INT UNSIGNED     NOT NULL,
  license         VARBINARY(30)    NOT NULL
);
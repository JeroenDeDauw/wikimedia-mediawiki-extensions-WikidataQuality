CREATE TABLE IF NOT EXISTS wdq_dump_information (
  source_item_id  INT UNSIGNED     PRIMARY KEY NOT NULL,
  import_date     TIMESTAMP        NOT NULL,
  language        VARBINARY(10)    NOT NULL,
  source_url      VARBINARY(1000)  NOT NULL,
  size            INT UNSIGNED     NOT NULL,
  license         VARBINARY(30)    NOT NULL
);
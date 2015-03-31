CREATE TABLE IF NOT EXISTS wdq_constraints
(
ID int NOT NULL AUTO_INCREMENT,
pid int,
constraint_name varchar(255),
class text,
constraint_status varchar(255),
comment text,
known_exception text,
group_by varchar(255),
item text,
list text,
mandatory varchar(255),
maximum_date varchar(255),
maximum_quantity varchar(255),
minimum_date varchar(255),
minimum_quantity varchar(255),
namespace varchar(255),
pattern varchar(255),
property varchar(255),
relation varchar(255),
snak varchar(255),
PRIMARY KEY (ID)
);
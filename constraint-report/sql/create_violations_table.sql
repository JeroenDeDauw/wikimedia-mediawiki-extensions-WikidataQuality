DROP TABLE IF EXISTS wdq_violations;

CREATE TABLE wdq_violations
(
id int PRIMARY KEY AUTO_INCREMENT,
pid int,
qid int,
claim_guid varchar(255),
constraint_name varchar(255),
additional_information varchar(255),
constraint_status varchar(255)
);
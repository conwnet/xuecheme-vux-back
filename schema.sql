
DROP TABLE if EXISTS `user`;
CREATE TABLE `user` (
  id integer PRIMARY KEY auto_increment,
  username varchar(127),
  password varchar(127),
  role integer DEFAULT 0,
  coach_mobile VARCHAR(127) DEFAULT 0,
  nick VARCHAR(127) DEFAULT '',
  age VARCHAR(127) DEFAULT '',
  addr VARCHAR(127) DEFAULT '',
  token varchar(127) DEFAULT '',
  timeout bigint DEFAULT 0
);

DROP TABLE if EXISTS `plan`;
CREATE TABLE `plan` (
  id INTEGER PRIMARY KEY auto_increment,
  name VARCHAR(127) DEFAULT '',
  val longtext
);

INSERT INTO plan (name, val) VALUES ('默认课表', '[]');

DROP TABLE if EXISTS `admin`;
CREATE TABLE `admin` (
  id INTEGER PRIMARY KEY auto_increment,
  username VARCHAR(127) DEFAULT '',
  password VARCHAR (127) DEFAULT '',
  power INTEGER DEFAULT 0
);

INSERT INTO admin (username, password) VALUES ('admin', 'd033e22ae348aeb5660fc2140aec35850c4da997');

DROP TABLE if EXISTS `relation`;
CREATE TABLE `relation` (
  id INTEGER PRIMARY KEY auto_increment,
  stu_id INTEGER DEFAULT 0,
  coa_id INTEGER DEFAULT 0
);

DROP TABLE if EXISTS `course`;
CREATE TABLE `course` (
  id INTEGER PRIMARY KEY auto_increment,
  year INTEGER DEFAULT 0,
  month INTEGER DEFAULT 0,
  date INTEGER DEFAULT 0,
  start INTEGER DEFAULT 0,
  end INTEGER DEFAULT 0,
  user_id INTEGER,
  coach_id INTEGER
)
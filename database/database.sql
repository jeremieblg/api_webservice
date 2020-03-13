create database api_db default character set utf8 default collate utf8_bin;

CREATE TABLE characters
( character_id INT NOT NULL AUTO_INCREMENT,
  first_name varchar(50) NOT NULL,
  last_name varchar(50) NOT NULL,
  hero_name varchar(50) NOT NULL,
  age INT NOT NULL,
  created datetime NOT NULL,
  modified datetime NOT NULL,
  PRIMARY KEY (character_id)
);

INSERT INTO characters (first_name, last_name, hero_name, age, created)
VALUES ('Bruce', 'Wayne', 'Batman', 45, '2020-03-10 09:00:00', '2020-03-10 09:00:00');

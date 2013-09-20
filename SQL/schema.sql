CREATE  TABLE 'dungeon'.'users2' (
  'id' INT NOT NULL AUTO_INCREMENT ,
  'username' VARCHAR(45) NOT NULL ,
  'password' VARCHAR(45) NULL ,
  'logged_in' TINYINT NOT NULL ,
  'room_coord' VARCHAR(10) NULL ,
  PRIMARY KEY (`id`) );


CREATE  TABLE 'dungeon'.'messages' (
  'id' INT NOT NULL AUTO_INCREMENT ,
  'user_id' INT NOT NULL ,
  'message_type' SMALLINT NOT NULL ,
  'message' TEXT NULL ,
  'room_coord' VARCHAR(10) NULL ,
  'target_user_id' INT NULL ,
  'time_added' DATETIME NOT NULL ,
  PRIMARY KEY (`id`) );
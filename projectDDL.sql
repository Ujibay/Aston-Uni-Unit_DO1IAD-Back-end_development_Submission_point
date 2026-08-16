/* 	CS1IAD, Summative assessment 2
    Apply your knowledge 3: Back-end development
	Data Definition Language
	Oliver Hannaford-Day Student ID: 260270485 */
	
CREATE DATABASE IF NOT EXISTS 'week12'
USE 'week12'

CREATE TABLE IF NOT EXISTS users (
	uid INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, 															-- User ID Number, auto incrementing and can not be empty.
	username VARCHAR(255) NOT NULL UNIQUE, 																	-- User name, can not be empty, must be unique.
	password VARCHAR(255) NOT NULL, 																		-- Password, can not be empty, is the hash and salt, not actual user password
	email VARCHAR(255) NOT NULL UNIQUE CHECK(email LIKE '%@%.%'), 											-- eMail address of user, can not be empty, must be unique and checked to be a valid *@*.* style address.
	PRIMARY KEY(`uid`) 																						-- Primary key is the user ID number.
);


CREATE TABLE IF NOT EXISTS projects (
	pid INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, 															-- Project id number, auto incrementing and can not be empty
	uid INTEGER UNSIGNED NOT NULL, 																			-- User ID from users table. Declared as foreign key below. Can not be empty.
	title VARCHAR(255) NOT NULL UNIQUE, 																	-- Project title, can not be blank and must be unique.
	shortDescription VARCHAR(255) NOT NULL DEFAULT 'New project', 											-- Short project description, can not be empty but defaults to New project
	startDate DATE, 																						-- Project start date can be empty as may not know when its planned to start.
	endDate DATE, 																							-- Project end date can also be empty.
	phase ENUM('design', 'development', 'testing', 'deployment', 'complete') NOT NULL DEFAULT 'design',		-- Project phase, custom ENUM, can not be empty and defaults to design phase.
	PRIMARY KEY (pid), 																						-- Primary key is the project ID number.
	FOREIGN KEY (uid) REFERENCES users(uid) ON UPDATE NO ACTION ON DELETE RESTRICT 							-- Foreign key is uid from users.
);
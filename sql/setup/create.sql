-- TO DO
--
-- 4.) Maybe make a table of acceptable table-field: combos that EAV can check self against
--

-- CREATE A TABLE TO HOUSE THE CURRENT STATE OF ANY ADDITIONAL ATTRIBUTE
--  
DROP TABLE IF EXISTS eav;;
CREATE TABLE IF NOT EXISTS eav (
	_id integer PRIMARY KEY AUTOINCREMENT,
	_table text not null,
	_table_id text not null,
	_field_name text not null,
	_field_value text not null,
	_date_of not null,
	unique(_table, _table_id, _field_name)
);;
-- --------------------------------
-- --------------------------------


-- CREATE TABLE TO HOUSE USERS
DROP TABLE IF EXISTS users;;
CREATE TABLE IF NOT EXISTS users (
	email text not null primary key,
	created integer not null default(strftime('%s','now')),
	isactive integer not null default 1,
	eav_field text,
	eav_value text
);;
-- --------------------------------
-- --------------------------------

-- CREATE TABLE TO HOUSE THE TYPES OF ROLES A PERSON MIGHT HAVE
DROP TABLE IF EXISTS roles;;
CREATE TABLE IF NOT EXISTS roles (
	_id integer PRIMARY KEY AUTOINCREMENT,
	name text not null,
	created integer not null default(strftime('%s','now')),
	isactive integer not null default 1,
	eav_field text,
	eav_value text,
	unique(name)
);;
-- --------------------------------
-- --------------------------------



-- CREATE TABLE TO HOUSE THE COURSES ONE MIGHT TAKE
DROP TABLE IF EXISTS courses;;
CREATE TABLE IF NOT EXISTS courses (
	_id integer PRIMARY KEY AUTOINCREMENT,
	name text not null,
	created integer not null default(strftime('%s','now')),
	isactive integer not null default 1,
	eav_field text,
	eav_value text,
	unique(name)
);;
-- --------------------------------
-- --------------------------------


-- CREATE A TABLE TO LINK USERS AND ROLES...
drop table if exists link_users_to_roles;;
CREATE TABLE IF NOT EXISTS link_users_to_roles (
	_id integer PRIMARY KEY AUTOINCREMENT,
	user_id text not null,
	role_id integer not null,
	eav_field text,
	eav_value text,
	unique(user_id, role_id),
	foreign key (user_id) references users(email) on delete cascade,
	foreign key (role_id) references roles(_id) on delete cascade
);;
-- --------------------------------
-- --------------------------------

-- CREATE A TABLE TO LINK USERS TO COURSES (guess what its called...)
drop table if exists link_users_to_courses;;
CREATE TABLE IF NOT EXISTS link_users_to_courses (
	_id integer PRIMARY KEY AUTOINCREMENT,
	user_id text not null,
	course_id integer not null,
	eav_field text,
	eav_value text,
	unique(user_id, course_id),
	foreign key (user_id) references users(email) on delete cascade,
	foreign key (course_id) references courses(_id) on delete cascade
);;
-- --------------------------------
-- --------------------------------

-- CREATE A TABLE TO ROLES AND COURSES...
drop table if exists link_roles_to_courses;;
CREATE TABLE IF NOT EXISTS link_roles_to_courses (
	_id integer PRIMARY KEY AUTOINCREMENT,
	course_id integer not null,
	role_id integer not null,
	eav_field text,
	eav_value text,
	unique(course_id, role_id),
	foreign key (course_id) references courses(_id) on delete cascade,
	foreign key (role_id) references roles(_id) on delete cascade
);;
-- --------------------------------
-- --------------------------------


CREATE TRIGGER users_after_insert_assign_role
AFTER INSERT
ON `users`
BEGIN

	INSERT INTO `link_users_to_roles`(`user_id`,`role_id`) 
		VALUES(
			NEW.email, 
			(SELECT _id from roles where name = 'general')
		);
 END;;
 
 
 

--
-- N.B.
-- The following are triggers that are created to help manage the EAV (entity-attribute-value) table
-- and are a total hack, and probably shouldn't be used, anti-patterns, blah-blah-blab
--
-- That said, here's what they do, and how they work...
-- When you want to add a new attribute to the eav table,
-- update the values `eav_field` and `eav_value` on the record you want to hit.
-- The tables will 'auto-magically' push those values into the eav table without question.
--
-- Keep in mind, it'll accept anything anywhere, so integrity is up to the API to keep s***
-- clean
--

-- EAV TRIGGER => users
CREATE TRIGGER users_after_update_push_eav
AFTER UPDATE
ON `users`
WHEN NEW.eav_field IS NOT NULL AND NEW.eav_value is not null 
BEGIN

	INSERT INTO `eav`(`_table`,`_table_id`,`_field_name`,`_field_value`, _date_of) 
		VALUES(
			'users', 
			NEW.email, 
			NEW.eav_field, 
			NEW.eav_value,
			datetime('now')
		) ON CONFLICT(_table, _table_id, _field_name) DO UPDATE SET _field_value = excluded._field_value, _date_of = datetime('now');

	UPDATE USERS set eav_field = null, eav_value = null 
		where email = new.email;
 END;;
  
-- EAV TRIGGER => courses
CREATE TRIGGER courses_after_update_push_eav
AFTER UPDATE
ON `courses`
WHEN NEW.eav_field IS NOT NULL AND NEW.eav_value is not null 
BEGIN
	INSERT INTO `eav`(`_table`,`_table_id`,`_field_name`,`_field_value`, `_date_of`) 
		VALUES('courses', NEW._id, NEW.eav_field, NEW.eav_value, datetime('now')) ON CONFLICT(_table, _table_id, _field_name) DO UPDATE SET _field_value = excluded._field_value, _date_of = datetime('now');
	
	UPDATE courses set eav_field = null, eav_value = null 
		where _id = new._id;
 END;;

-- EAV TRIGGER => roles
CREATE TRIGGER roles_after_update_push_eav
AFTER UPDATE
ON `roles`
WHEN NEW.eav_field IS NOT NULL AND NEW.eav_value is not null 
BEGIN
	INSERT INTO `eav`(`_table`,`_table_id`,`_field_name`,`_field_value`, `_date_of`) 
		VALUES('roles', NEW._id, NEW.eav_field, NEW.eav_value, datetime('now')) ON CONFLICT(_table, _table_id, _field_name) DO UPDATE SET _field_value = excluded._field_value, _date_of = datetime('now');
	
	UPDATE roles set eav_field = null, eav_value = null 
		where _id = new._id;
 END;;


-- EAV TRIGGER => link_users_to_roles
CREATE TRIGGER link_users_to_roles_after_update_push_eav
AFTER UPDATE
ON `link_users_to_roles`
WHEN NEW.eav_field IS NOT NULL AND NEW.eav_value is not null 
BEGIN
	INSERT INTO `eav`(`_table`,`_table_id`,`_field_name`,`_field_value`, `_date_of`) 
		VALUES('link_users_to_roles', NEW._id, NEW.eav_field, NEW.eav_value, datetime('now')) ON CONFLICT(_table, _table_id, _field_name) DO UPDATE SET _field_value = excluded._field_value, _date_of = datetime('now');
	
	UPDATE link_users_to_roles set eav_field = null, eav_value = null 
		where _id = new._id;
 END;;

-- EAV TRIGGER => link_users_to_courses
CREATE TRIGGER link_users_to_courses_after_update_push_eav
AFTER UPDATE
ON `link_users_to_courses`
WHEN NEW.eav_field IS NOT NULL AND NEW.eav_value is not null 
BEGIN
	INSERT INTO `eav`(`_table`,`_table_id`,`_field_name`,`_field_value`, `_date_of`) 
		VALUES('link_users_to_courses', NEW._id, NEW.eav_field, NEW.eav_value, datetime('now')) ON CONFLICT(_table, _table_id, _field_name) DO UPDATE SET _field_value = excluded._field_value, _date_of = datetime('now');
	
	UPDATE link_users_to_courses set eav_field = null, eav_value = null 
		where _id = new._id;
 END;;

-- EAV TRIGGER => link_roles_to_courses
CREATE TRIGGER link_roles_to_courses_after_update_push_eav
AFTER UPDATE
ON `link_roles_to_courses`
WHEN NEW.eav_field IS NOT NULL AND NEW.eav_value is not null 
BEGIN
	INSERT INTO `eav`(`_table`,`_table_id`,`_field_name`,`_field_value`, `_date_of`) 
		VALUES('link_roles_to_courses', NEW._id, NEW.eav_field, NEW.eav_value, datetime('now')) ON CONFLICT(_table, _table_id, _field_name) DO UPDATE SET _field_value = excluded._field_value, _date_of = datetime('now');
	
	UPDATE link_roles_to_courses set eav_field = null, eav_value = null 
		where _id = new._id;
 END;;


-- CREATE TABLE TO HOUSE USERS
DROP TABLE IF EXISTS users;;
CREATE TABLE IF NOT EXISTS users (
	email text not null primary key,
	created integer not null default(strftime('%s','now')),
	isactive integer not null default 1
);;


-- CREATE TABLE TO HOUSE THE TYPES OF ROLES A PERSON MIGHT HAVE
DROP TABLE IF EXISTS roles;;
CREATE TABLE IF NOT EXISTS roles (
	_id integer PRIMARY KEY AUTOINCREMENT,
	name text not null,
	created integer not null default(strftime('%s','now')),
	isactive integer not null default 1,
	unique(name)
);;


-- CREATE TABLE TO HOUSE THE COURSES ONE MIGHT TAKE
DROP TABLE IF EXISTS courses;;
CREATE TABLE IF NOT EXISTS courses (
	_id integer PRIMARY KEY AUTOINCREMENT,
	name text not null,
	created integer not null default(strftime('%s','now')),
	isactive integer not null default 1,
	unique(name)
);;

-- CREATE A TABLE TO HOUSE THE CURRENT STATE OF ANY ADDITIONAL ATTRIBUTE
--  
DROP TABLE IF EXISTS eav;;
CREATE TABLE IF NOT EXISTS eav (
	_id integer PRIMARY KEY AUTOINCREMENT,
	_table text not null,
	_table_id text not null,
	_field_name text not null,
	_field_value text not null,
	unique(_table, _table_id, _field_name)
);;

-- CREATE A TABLE TO LINK USERS AND ROLES...
drop table if exists link_users_to_roles;;
CREATE TABLE IF NOT EXISTS link_users_to_roles (
	_id integer PRIMARY KEY AUTOINCREMENT,
	user_id text not null,
	role_id integer not null,
	unique(user_id, role_id),
	foreign key (user_id) references users(email) on delete cascade,
	foreign key (role_id) references roles(_id) on delete cascade
);;

-- CREATE A TABLE TO LINK USERS TO COURSES (guess what its called...)
drop table if exists link_users_to_courses;;
CREATE TABLE IF NOT EXISTS link_users_to_courses (
	_id integer PRIMARY KEY AUTOINCREMENT,
	user_id text not null,
	course_id integer not null,
	unique(user_id, course_id),
	foreign key (user_id) references users(email) on delete cascade,
	foreign key (course_id) references courses(_id) on delete cascade
);;

-- CREATE A TABLE TO ROLES AND COURSES...
drop table if exists link_roles_to_courses;;
CREATE TABLE IF NOT EXISTS link_roles_to_courses (
	_id integer PRIMARY KEY AUTOINCREMENT,
	course_id integer not null,
	role_id integer not null,
	unique(course_id, role_id),
	foreign key (course_id) references courses(_id) on delete cascade,
	foreign key (role_id) references roles(_id) on delete cascade
);;


CREATE TRIGGER users_after_insert_assign_roles
 AFTER INSERT
 ON `users`
 BEGIN
	INSERT INTO `link_users_to_roles`(`user_id`,`role_id`) VALUES(NEW.email, 1);
 END;;


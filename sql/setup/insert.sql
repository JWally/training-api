-- BUILD 2 ROLES: (admin, general)
insert into `roles`(`name`) values('general'),('admin');;

-- CREATE ME, A NAME, I CALL MY SELF
insert into `users`(`email`) values ('justin@argenticmgmt.net');;
insert into `users`(`email`) values ('steve@argenticmgmt.net');;
insert into `users`(`email`) values ('Larry@argenticmgmt.net');;
insert into `users`(`email`) values ('jimmy@argenticmgmt.net');;

-- I WANT TO BE AN ADMIN

insert into `link_users_to_roles`(`user_id`,`role_id`) 
	select 'justin@argenticmgmt.net', oid
	from roles
	where `name` = 'admin';;


-- CREATE SOME FUGGIN COURSES AND WHAT NOT...
insert into `courses`(`name`) values
('NUCL 200 Introduction to Nuclear Engineering'),
('NUCL 205 Nuclear Engineering Undergraduate Laboratory I'),
('NUCL 273 Mechanics of Materials'),
('NUCL 298, 398, 498, 696 Seminar'),
('NUCL 300 Nuclear Structure and Radiation Interactions'),
('NUCL 305 Nuclear Engineering Undergraduate Laboratory I'),
('NUCL 310 Introduction to Neutron Physics'),
('NUCL 320 Introduction to Materials for Nuclear Applications'),
('NUCL 350 Nuclear Thermal Hydraulics I'),
('NUCL 351 Nuclear Thermal Hydraulics II'),
('NUCL 355 Nuclear Thermal Hydraulics Laboratory'),
('NUCL 402 Engineering of Nuclear Power Systems'),
('NUCL 410 Introduction to Reactor Theory and Applications'),
('NUCL 449 Senior Design Proposal'),
('NUCL 450 Design in Nuclear Engineering');;

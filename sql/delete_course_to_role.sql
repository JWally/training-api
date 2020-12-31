delete from link_roles_to_courses
where course_id = :course_id
and role_id = :role_id;
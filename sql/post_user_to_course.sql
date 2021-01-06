insert into link_users_to_courses(user_id, course_id) values(:user_id, :course_id)
on conflict(user_id, course_id) do nothing;;

select * from link_users_to_courses
where user_id = :user_id
and course_id = :course_id
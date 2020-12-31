delete from link_users_to_roles
where user_id = :user_id
and role_id = :role_id;
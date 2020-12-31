insert into users(email) values(:user_id)
on conflict(email) do nothing;
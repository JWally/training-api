select rr.name as role_name

from users uu

inner join link_users_to_roles lutr
    on lutr.user_id = uu.email
    
inner join roles rr
    on rr.oid = lutr.role_id
    
where uu.email = :email
;
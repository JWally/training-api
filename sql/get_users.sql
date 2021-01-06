select
    uu.email,
    datetime(uu.created,'unixepoch','localtime') as created,
    case when uu.isactive = 1
		then 'YES'
		else 'NO'
	end as isactive,
    rr._id as role_id,
	rr.name as role_name
    
from users uu

left outer join link_users_to_roles utr
    on utr.user_id = uu.email
    
left outer join roles rr
    on rr._id = utr.role_id
    
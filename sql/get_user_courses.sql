
select distinct
	cc._id,
    uu.email,
    cc.name,
    _status._field_value as `status`
    -- _status._date_of
    
from users uu

left outer join link_users_to_roles lutr
    on lutr.user_id = uu.email
    
left outer join link_roles_to_courses lrtc
    on lrtc.role_id = lutr.role_id
    
left outer join courses cc
    on cc._id = lrtc.course_id
    
left outer join link_users_to_courses lutc
    on lutc.user_id = uu.email
    and lutc.course_id = cc._id

left outer join eav _status
    on _status._field_name = 'status'
    and _status._table = 'link_users_to_courses'
    and _status._table_id = lutc._id
	
where 1 = 1
and uu.email like :user_id
and cc.name is not null
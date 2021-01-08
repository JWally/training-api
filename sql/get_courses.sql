select
    cc._id as course_id,
    cc.name as course_name,
    datetime(cc.created,'unixepoch','localtime') as created,
    case when cc.isactive = 1
        then 'YES'
        else 'NO'
    end as isactive,
    rr._id as role_id,
    rr.name as role_name,
	_duedate._field_value as duedate
    
from courses cc

left outer join link_roles_to_courses lrtc
    on lrtc.course_id = cc._id
    
left outer join roles rr
    on rr._id = lrtc.role_id
	
left outer join eav _duedate
    on _duedate._field_name = 'duedate'
    and _duedate._table = 'courses'
    and _duedate._table_id = cc._id

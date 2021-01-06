update link_users_to_courses
	set eav_field = 'status', eav_value = :value
	
where _id = :id;
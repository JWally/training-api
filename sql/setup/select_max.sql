select
	cc.id,
	cc.name,
	cx.id as id2
	
from cats cc

inner join cats cx
	on cx.id = cc.id
	and cx.id = (
		select max(xx.id)
		from cats xx
		where xx.name = cx.name
	)
;
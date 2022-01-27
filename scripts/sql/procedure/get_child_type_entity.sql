drop procedure if exists get_child_type_entity;
DELIMITER $$
create procedure get_child_type_entity(in in_id int)
begin
  set @list     = in_id;
	set @id_entity = in_id;

  set @sql = '
    select c.id, c.name, c.slug from companies c, types_entities te, types tp 
		where c.`status` = "Aprobado" 
		and c.type_entity_id = te.id 
		and te.id = @id_entity
		and te.`status` = "Publicado" 
		and te.type_id = tp.id 
		and tp.slug = "oferta" 
		ORDER BY c.`name` asc
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$
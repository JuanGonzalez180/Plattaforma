drop procedure if exists get_child_type_category_service_admin;
DELIMITER $$
create procedure get_child_type_category_service_admin(in in_id int)
begin
  set @list = in_id;
  set @parents = @list;

  repeat
    set @sql = '
      select group_concat(id) into @children
      from category_services
      where parent_id in ({parents})
    ';
    set @sql = replace(@sql, '{parents}', @parents);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @list, @children);
    set @parents = @children;
  until (@children is null) end repeat;

  set @child = in_id;
  repeat
    set @sql = '
      select parent_id into @parent
      from category_services
      where id = ({child})
    ';
    set @sql = replace(@sql, '{child}', @child);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @parent, @list);
    set @child = @parent;
  until (@parent is null) end repeat;

  set @sql = '
    select id, name, description, parent_id
    from category_services
    where id in ({list})
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$

drop procedure if exists get_child_type_category_service;
DELIMITER $$
create procedure get_child_type_category_service(in in_id int)
begin
  set @list = in_id;
  set @parents = @list;

  repeat
    set @sql = '
      select group_concat(id) into @children
      from category_services
      where parent_id in ({parents}) and status = "Publicado"
    ';
    set @sql = replace(@sql, '{parents}', @parents);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @list, @children);
    set @parents = @children;
  until (@children is null) end repeat;

  set @child = in_id;
  repeat
    set @sql = '
      select parent_id into @parent
      from category_services
      where id = ({child}) and status = "Publicado"
    ';
    set @sql = replace(@sql, '{child}', @child);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @parent, @list);
    set @child = @parent;
  until (@parent is null) end repeat;

  set @sql = '
    select id, name, description, parent_id
    from category_services
    where id in ({list}) and status = "Publicado"
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$

drop procedure if exists get_child_type_categoty_admin;
DELIMITER $$
create procedure get_child_type_categoty_admin(in in_id int)
begin
  set @list = in_id;
  set @parents = @list;

  repeat
    set @sql = '
      select group_concat(id) into @children
      from categories
      where parent_id in ({parents})
    ';
    set @sql = replace(@sql, '{parents}', @parents);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @list, @children);
    set @parents = @children;
  until (@children is null) end repeat;

  set @child = in_id;
  repeat
    set @sql = '
      select parent_id into @parent
      from categories
      where id = ({child})
    ';
    set @sql = replace(@sql, '{child}', @child);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @parent, @list);
    set @child = @parent;
  until (@parent is null) end repeat;

  set @sql = '
    select id, name, description, parent_id
    from categories
    where id in ({list})
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$


drop procedure if exists get_child_type_categoty;
DELIMITER $$
create procedure get_child_type_categoty(in in_id int)
begin
  set @list = in_id;
  set @parents = @list;

  repeat
    set @sql = '
      select group_concat(id) into @children
      from categories
      where parent_id in ({parents}) and status = "Publicado"
    ';
    set @sql = replace(@sql, '{parents}', @parents);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @list, @children);
    set @parents = @children;
  until (@children is null) end repeat;

  set @child = in_id;
  repeat
    set @sql = '
      select parent_id into @parent
      from categories
      where id = ({child}) and status = "Publicado"
    ';
    set @sql = replace(@sql, '{child}', @child);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @parent, @list);
    set @child = @parent;
  until (@parent is null) end repeat;

  set @sql = '
    select id, name, description, parent_id
    from categories
    where id in ({list}) and status = "Publicado"
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$

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

drop procedure if exists get_child_type_project_admin;
DELIMITER $$
create procedure get_child_type_project_admin(in in_id int)
begin
  set @list     = in_id;
  set @parents  = @list;

  repeat
    set @sql = '
      select group_concat(id) into @children
      from type_projects
      where parent_id in ({parents})
    ';
    set @sql = replace(@sql, '{parents}', @parents);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @list, @children);
    set @parents = @children;
  until (@children is null) end repeat;

  set @child = in_id;
  repeat
    set @sql = '
      select parent_id into @parent
      from type_projects
      where id = ({child})
    ';
    set @sql = replace(@sql, '{child}', @child);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @parent, @list);
    set @child = @parent;
  until (@parent is null) end repeat;

  set @sql = '
    select id, name, description, parent_id
    from type_projects
    where id in ({list})
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$


drop procedure if exists get_child_type_project;
DELIMITER $$
create procedure get_child_type_project(in in_id int)
begin
  set @list     = in_id;
  set @parents  = @list;

  repeat
    set @sql = '
      select group_concat(id) into @children
      from type_projects
      where parent_id in ({parents}) and status = "Publicado"
    ';
    set @sql = replace(@sql, '{parents}', @parents);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @list, @children);
    set @parents = @children;
  until (@children is null) end repeat;

  set @child = in_id;
  repeat
    set @sql = '
      select parent_id into @parent
      from type_projects
      where id = ({child}) and status = "Publicado"
    ';
    set @sql = replace(@sql, '{child}', @child);
    prepare stmt from @sql;
    execute stmt;
    set @list = concat_ws(',', @parent, @list);
    set @child = @parent;
  until (@parent is null) end repeat;

  set @sql = '
    select id, name, description, parent_id
    from type_projects
    where id in ({list}) and status = "Publicado"
  ';
  set @sql = replace(@sql, '{list}', @list);
  prepare stmt from @sql;
  execute stmt;
END;$$

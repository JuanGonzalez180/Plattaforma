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

-- cleanning for unused application
delete from acl where id_application not in (select id from application);
delete from action where id_application not in (select id from application);
delete from permission where id_acl not in (select id from acl);
delete from paramv where appid  not in (select id from application);
delete from paramdef where appid  not in (select id from application);
DELETE FROM paramv using  paramdef where paramv.name = paramdef.name and paramdef.isglob='Y' and paramv.appid != paramdef.appid;
--delete from paramv using  paramdef where type != 'G' and paramv.name =paramdef.name and paramv.appid= paramdef.appid and paramdef.isglob = 'Y' and paramdef.isuser != 'Y';
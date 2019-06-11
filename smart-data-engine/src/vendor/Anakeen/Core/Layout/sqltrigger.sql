-- BEFORE INSERT OR UPDATE ON family.*
CREATE OR REPLACE FUNCTION "doc[docid]_fieldvalues"() RETURNS trigger AS $$
declare
  av text;
begin

av:='{';
[BLOCK ATTRFIELD]
if not NEW.[attrid] isnull then
  av:= av || '"[attrid]":' || to_json(NEW.[attrid]::[casttype]) || ',';
end if;[ENDBLOCK ATTRFIELD]
if (char_length(av) > 1) then
  av:= substring(av for char_length(av) - 1) || '}';
else
  av:=  '{}';
end if;
--RAISE NOTICE 'avalues %',av;
NEW.fieldvalues := av;

return NEW;
end;
$$ LANGUAGE 'plpgsql';

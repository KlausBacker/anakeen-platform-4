-- BEFORE INSERT OR UPDATE ON family.*
CREATE OR REPLACE FUNCTION "doc[docid]_fieldvalues"() RETURNS trigger AS $$
declare
  av jsonb;
begin
[IFNOT ISEMPTY][BLOCK FITHTY]
av:= [IFNOT first]av || [ENDIF first]jsonb_build_object([fields]);[ENDBLOCK FITHTY]
NEW.fieldvalues := jsonb_strip_nulls(av);[ENDIF ISEMPTY]
return NEW;
end;
$$ LANGUAGE 'plpgsql';

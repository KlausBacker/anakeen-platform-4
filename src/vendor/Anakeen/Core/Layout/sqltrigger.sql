


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


CREATE OR REPLACE FUNCTION searchvalues[docid]() RETURNS trigger AS $$
		declare
		  pos int;
  		good bool;
  		reallyUpdated bool;
		begin

		if NEW.doctype != 'T' then
				reallyUpdated := (TG_OP = 'INSERT') OR (NEW.fieldvalues != OLD.fieldvalues) OR (COALESCE(NEW.svalues,'') != COALESCE(OLD.svalues,'')) OR (NEW.fulltext is null);

			if reallyUpdated then
					-- Plain text Part
				  NEW.svalues:=COALESCE(NEW.svalues, '');

          if (TG_OP = 'UPDATE' AND NEW.svalues = OLD.svalues) then
            -- reset to display value part
            pos := position('ΞΞ' in NEW.svalues);
            NEW.svalues:=substring(OLD.svalues from 0 for pos);
            NEW.svalues:=COALESCE(NEW.svalues, '');
          end if;
          -- Fulltext Part

          begin
              [BLOCK FILEATTR]
              if NEW.[vecid] is null or (NEW.[vecid]='' and NEW.[attrid]!='') then
                NEW.[vecid] := setweight2(NEW.[attrid]);
              end if; [ENDBLOCK FILEATTR]

              NEW.fulltext:=setweight2(NEW.title, 'A') || setweight2(NEW.svalues, 'C') ||

              [BLOCK ABSATTR]
                setweight2(NEW.[attrid]::text, 'B') ||[ENDBLOCK ABSATTR]
              [BLOCK FILEATTR2]
                NEW.[vecid] ||[ENDBLOCK FILEATTR2]
              [BLOCK FULLTEXT_C]
                setweight2(NEW.[attrid]::text, 'C') ||[ENDBLOCK FULLTEXT_C]
                setweight2('', 'C');

                EXCEPTION
                  WHEN OTHERS THEN
                  RAISE NOTICE 'fulltext not set %',NEW.id;
          end;

          -- Plain text Part

          NEW.svalues := NEW.svalues || ' ΞΞ ' ||
          [BLOCK SEARCHFIELD] COALESCE(NEW.[attrid]::text || '£', '') ||
          [ENDBLOCK SEARCHFIELD]
          '£';

			end if;
		end if;
		return NEW;
		end;
$$ LANGUAGE 'plpgsql';


-- Convert atags text to json
create or replace function to_atags(text)
returns jsonb as $$
declare
  satags alias for $1;
  arr text[];
  wt text;
   m   text;
   r jsonb;
begin
  r:='{}';
  IF satags is not null THEN
    arr:=  regexp_split_to_array(satags, E'\n');

    FOREACH m IN ARRAY arr
     LOOP
        r := r || jsonb_build_object(trim(m), true);
     END LOOP;
  END IF;
  return (r);
end;
$$ language 'plpgsql';


create or replace function text_to_array(text)
returns text[] as $$
declare
  svalues alias for $1;
  r text[];
  i integer ;
begin
  r:=null;
  IF svalues is not null THEN
    r:=  regexp_split_to_array(svalues, E'\n');
    FOR i IN array_lower(r, 1) .. array_upper(r, 1) LOOP
        IF r[i] = '' or r[i] = ' ' or r[i] = E'\t' or r[i] = E'\u00a0' THEN
          r[i] := null;
        END IF;
    END LOOP;
  END IF;
  return (r);
end;
$$ language 'plpgsql';


create or replace function longtext_to_array(text)
returns text[] as $$
declare
  svalues alias for $1;
  r text[];
  i integer ;
begin
  r:=null;
  IF svalues is not null THEN
    r:=  regexp_split_to_array(svalues, E'\n');
    FOR i IN array_lower(r, 1) .. array_upper(r, 1) LOOP
        IF r[i] = '' or r[i] = ' ' or r[i] = E'\t'  or r[i] = E'\u00a0' THEN
          r[i] := null;
        ELSE
          r[i]=replace( r[i], '<BR>', E'\n');
        END IF;
    END LOOP;
  END IF;
  return (r);
end;
$$ language 'plpgsql';

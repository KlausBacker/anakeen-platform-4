drop aggregate if exists tsvector_agg;
create aggregate tsvector_agg (tsvector) (
 STYPE = pg_catalog.tsvector,
 SFUNC = pg_catalog.tsvector_concat,
 INITCOND = ''
);

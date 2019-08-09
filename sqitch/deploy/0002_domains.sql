-- Deploy geoname:0002_domains to pg
-- requires: 0001_extensions

BEGIN;

  --
  -- Camel Case
  --
  
  CREATE OR REPLACE FUNCTION public.toCamelCase(TEXT) RETURNS TEXT AS
  $$
  SELECT replace(initcap(regexp_replace(unaccent($1), '[^A-Za-z0-9]+', ' ', 'g')), ' ', '');
  $$ LANGUAGE sql IMMUTABLE;

  --
  -- Global ID
  --

  CREATE DOMAIN public.global_id AS LTREE CHECK (nlevel(VALUE) = 3);

  CREATE OR REPLACE FUNCTION gid_schema(global_id) RETURNS TEXT AS
  $$
  SELECT ltree2text(subpath($1,0,1));
  $$ LANGUAGE sql IMMUTABLE;

  CREATE OR REPLACE FUNCTION gid_table(global_id) RETURNS TEXT AS
  $$
  SELECT ltree2text(subpath($1,1,1));
  $$ LANGUAGE sql IMMUTABLE;

  CREATE OR REPLACE FUNCTION gid_entity_id(global_id) RETURNS TEXT AS
  $$
  SELECT ltree2text(subpath($1,2,1));
  $$ LANGUAGE sql IMMUTABLE;

  CREATE OR REPLACE FUNCTION public.generate_gid() RETURNS TRIGGER AS
  $$
  BEGIN
    IF NEW.id IS NOT NULL THEN
      NEW.gid := CONCAT(tg_table_schema, '.', tg_table_name, '.', NEW.id)::global_id;
    END IF;

    RETURN NEW;
  END;
  $$ LANGUAGE plpgsql IMMUTABLE;

  --
  -- Tag 3D
  --

  CREATE DOMAIN public.tag3d AS LTREE CHECK (nlevel(VALUE) = 3);

  CREATE OR REPLACE FUNCTION tag3d_cat(tag3d) RETURNS TEXT AS
  $$
  SELECT ltree2text(subpath($1,0,1));
  $$ LANGUAGE sql IMMUTABLE;

  CREATE OR REPLACE FUNCTION tag3d_sub_cat(tag3d) RETURNS TEXT AS
  $$
  SELECT ltree2text(subpath($1,1,1));
  $$ LANGUAGE sql IMMUTABLE;

  CREATE OR REPLACE FUNCTION tag3d_tag(tag3d) RETURNS TEXT AS
  $$
  SELECT ltree2text(subpath($1,2,1));
  $$ LANGUAGE sql IMMUTABLE;

  CREATE OR REPLACE FUNCTION tag3d(
    category TEXT,
    sub_category TEXT,
    tag TEXT
  ) RETURNS tag3d AS
  $$
  SELECT
    CASE (public.toCamelCase($1) IS NULL
      OR public.toCamelCase($2) IS NULL
      OR public.toCamelCase($3) IS NULL
      OR public.toCamelCase($1) = ''
      OR public.toCamelCase($2) = ''
      OR public.toCamelCase($3) = ''
      )
      WHEN TRUE THEN NULL::tag3d
      ELSE (text2ltree(
          COALESCE(public.toCamelCase($1), '_') || '.' ||
          COALESCE(public.toCamelCase($2), '_') || '.' ||
          COALESCE(public.toCamelCase($3), '_')
        ))::tag3d
      END;
  $$ LANGUAGE sql IMMUTABLE;

  --
  -- Tag 3D Array
  --

  CREATE OR REPLACE FUNCTION check_tag3d_array_validity(LTREE[]) RETURNS BOOLEAN AS
  $$
  DECLARE
    tag TEXT;
  BEGIN
    FOR tag IN SELECT unnest($1) LOOP
      PERFORM tag::public.tag3d;
    END LOOP;

    RETURN TRUE;

  EXCEPTION
    WHEN OTHERS THEN RETURN FALSE;
  END;
  $$ LANGUAGE plpgsql;

  CREATE DOMAIN public.tag3d_array AS LTREE[] CHECK ((check_tag3d_array_validity(VALUE)));

  CREATE OR REPLACE FUNCTION get_filtered_tags(tag3d_array, lquery) RETURNS tag3d_array AS
  $$
  SELECT COALESCE(array_agg(tag)::tag3d_array, ARRAY[]::tag3d_array) FROM (SELECT unnest($1) tag) t1 WHERE tag ~ $2;
  $$ IMMUTABLE LANGUAGE sql;

  CREATE OR REPLACE FUNCTION get_first_filtered_tag(tag3d_array, lquery) RETURNS tag3d AS
  $$
  SELECT tag::tag3d FROM (SELECT unnest($1) tag) t1 WHERE tag ~ $2 LIMIT 1;
  $$ IMMUTABLE LANGUAGE sql;

COMMIT;

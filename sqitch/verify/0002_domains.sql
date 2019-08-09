-- Verify geoname:0002_domains on pg

BEGIN;

  --
  -- Verify domains
  --

  SELECT 1/COUNT(*)
  FROM pg_catalog.pg_type
  WHERE typtype = 'd' AND typname = 'global_id';

  SELECT 1/COUNT(*)
  FROM pg_catalog.pg_type
  WHERE typtype = 'd' AND typname = 'tag3d';

  SELECT 1/COUNT(*)
  FROM pg_catalog.pg_type
  WHERE typtype = 'd' AND typname = 'tag3d_array';

  --
  -- Verify functions
  --

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'tocamelcase'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'gid_schema'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'gid_table'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'gid_entity_id'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'generate_gid'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'tag3d_cat'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'tag3d_sub_cat'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'tag3d_tag'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'tag3d'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'check_tag3d_array_validity'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'get_filtered_tags'
  ORDER BY 1;

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'public'
    AND routine_name = 'get_first_filtered_tag'
  ORDER BY 1;

ROLLBACK;

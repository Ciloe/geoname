-- Verify geoname:0004_executive on pg

BEGIN;

  SELECT 1/COUNT(*) FROM information_schema.schemata WHERE schema_name = 'executive';

  SELECT 1/COUNT(*) FROM information_schema.tables
  WHERE table_schema = 'executive' AND table_name = 'localization';

  SELECT 1/COUNT(*) FROM information_schema.routines
  WHERE routine_type = 'FUNCTION' AND specific_schema = 'executive'
    AND routine_name = 'localization_insert_or_update';

  SELECT 1/COUNT(*) FROM information_schema.triggers
  WHERE trigger_name = 'generate_gid' AND event_manipulation = 'INSERT'
    AND event_object_table = 'localization';

  SELECT 1/COUNT(*) FROM information_schema.triggers
  WHERE trigger_name = 'generate_gid' AND event_manipulation = 'UPDATE'
    AND event_object_table = 'localization';

  SELECT 1/COUNT(*) FROM information_schema.triggers
  WHERE trigger_name = 'localization_insert_or_update' AND event_manipulation = 'INSERT'
    AND event_object_table = 'localization';

  SELECT 1/COUNT(*) FROM information_schema.triggers
  WHERE trigger_name = 'localization_insert_or_update' AND event_manipulation = 'UPDATE'
    AND event_object_table = 'localization';

  SELECT 1/COUNT(*) FROM pg_indexes WHERE schemaname = 'executive'
    AND indexname = 'executive_localization_coordinates_idx';
  SELECT 1/COUNT(*) FROM pg_indexes WHERE schemaname = 'executive'
    AND indexname = 'executive_localization_id_parent_idx';
  SELECT 1/COUNT(*) FROM pg_indexes WHERE schemaname = 'executive'
    AND indexname = 'executive_localization_path_gist_idx';
  SELECT 1/COUNT(*) FROM pg_indexes WHERE schemaname = 'executive'
    AND indexname = 'executive_localization_path_idx';
  SELECT 1/COUNT(*) FROM pg_indexes WHERE schemaname = 'executive'
    AND indexname = 'executive_localization_type_idx';

ROLLBACK;

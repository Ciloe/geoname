-- Revert geoname:0004_executive from pg

BEGIN;

  DROP TRIGGER IF EXISTS localization_insert_or_update ON executive.localization;
  DROP TRIGGER IF EXISTS generate_gid ON executive.localization;

  DROP FUNCTION IF EXISTS executive.localization_insert_or_update CASCADE;

  DROP INDEX IF EXISTS executive.executive_localization_id_parent_idx;
  DROP INDEX IF EXISTS executive.executive_localization_type_idx;
  DROP INDEX IF EXISTS executive.executive_localization_coordinates_idx;
  DROP INDEX IF EXISTS executive.executive_localization_path_gist_idx;
  DROP INDEX IF EXISTS executive.executive_localization_path_idx;

  DROP TABLE IF EXISTS executive.localization;

  DROP SCHEMA IF EXISTS executive;

COMMIT;

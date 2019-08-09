-- Revert geoname:0003_abstract from pg

BEGIN;

  DROP TABLE IF EXISTS abstract.entity CASCADE;
  DROP TABLE IF EXISTS abstract.entity_datable CASCADE;
  DROP TABLE IF EXISTS abstract.entity_identifiable CASCADE;

  DROP SCHEMA IF EXISTS abstract CASCADE;

COMMIT;

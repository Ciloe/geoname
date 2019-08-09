-- Verify geoname:0003_abstract on pg

BEGIN;

  SELECT 1/COUNT(*) FROM information_schema.schemata WHERE schema_name = 'abstract';

  SELECT 1/COUNT(*) FROM information_schema.tables
  WHERE table_schema = 'abstract' AND table_name = 'entity_datable';

  SELECT 1/COUNT(*) FROM information_schema.tables
  WHERE table_schema = 'abstract' AND table_name = 'entity';

  SELECT 1/COUNT(*) FROM information_schema.tables
  WHERE table_schema = 'abstract' AND table_name = 'entity_identifiable';

ROLLBACK;

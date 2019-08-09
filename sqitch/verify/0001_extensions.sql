-- Verify geoname:0001_extensions on pg

BEGIN;

  SELECT 1/COUNT(*) FROM pg_extension WHERE extname = 'ltree';
  SELECT 1/COUNT(*) FROM pg_extension WHERE extname = 'postgis';
  SELECT 1/COUNT(*) FROM pg_extension WHERE extname = 'unaccent';
  SELECT 1/COUNT(*) FROM pg_extension WHERE extname = 'citext';
  SELECT 1/COUNT(*) FROM pg_extension WHERE extname = 'uuid-ossp';

ROLLBACK;

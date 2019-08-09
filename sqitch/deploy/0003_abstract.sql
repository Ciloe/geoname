-- Deploy geoname:0003_abstract to pg
-- requires: 0002_domains

BEGIN;

  CREATE SCHEMA IF NOT EXISTS abstract;

  CREATE TABLE IF NOT EXISTS abstract.entity_identifiable (
    id BIGINT NOT NULL,
    gid GLOBAL_ID NOT NULL,
    uuid UUID NOT NULL
  );

  CREATE TABLE IF NOT EXISTS abstract.entity_datable (
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now(),
    deleted_at TIMESTAMPTZ DEFAULT NULL
  );

  CREATE TABLE IF NOT EXISTS abstract.entity (
    data JSONB DEFAULT NULL,
    alt JSONB DEFAULT NULL,

    active BOOLEAN DEFAULT FALSE
  ) INHERITS (abstract.entity_identifiable, abstract.entity_datable);

COMMIT;

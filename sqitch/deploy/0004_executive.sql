-- Deploy geoname:0004_executive to pg
-- requires: 0003_abstract

SET client_min_messages = error;

BEGIN;

  CREATE SCHEMA IF NOT EXISTS executive;

  CREATE TABLE IF NOT EXISTS executive.localization (
    id BIGSERIAL PRIMARY KEY,

    name TEXT,
    type TAG3D NOT NULL,
    tags TAG3D_ARRAY NOT NULL,

    path LTREE,

    id_parent BIGINT REFERENCES executive.localization(id)
  ) INHERITS (abstract.entity, abstract.entity_geometry);

  CREATE INDEX IF NOT EXISTS executive_localization_id_parent_idx
    ON executive.localization(id_parent);
  CREATE UNIQUE INDEX IF NOT EXISTS executive_localization_uuid_idx
    ON executive.localization(uuid);
  CREATE INDEX IF NOT EXISTS executive_localization_geo_name_idx
    ON executive.localization(((alt->>'geoname')::INT));
  CREATE INDEX IF NOT EXISTS executive_localization_type_idx
    ON executive.localization USING GIST (type);
  CREATE INDEX IF NOT EXISTS executive_localization_tags_idx
    ON executive.localization USING GIST (tags);
  CREATE INDEX IF NOT EXISTS executive_localization_coordinates_idx
    ON executive.localization USING GIST (coordinates);
  CREATE INDEX IF NOT EXISTS executive_localization_path_gist_idx
    ON executive.localization USING GIST (path);
  CREATE INDEX IF NOT EXISTS executive_localization_path_idx
    ON executive.localization USING BTREE (path);

  CREATE OR REPLACE FUNCTION executive.localization_insert_or_update() RETURNS TRIGGER AS
  $$
  BEGIN
    IF (TG_OP = 'INSERT' OR NEW.id_parent IS DISTINCT FROM OLD.id_parent) THEN
      IF NEW.id_parent IS NULL THEN
        NEW.path = text2ltree(NEW.id::TEXT);
      ELSE
        SELECT path || NEW.id::TEXT
        FROM executive.localization l
        WHERE l.id = NEW.id_parent
          INTO NEW.path;
      END IF;
    END IF;

    RETURN NEW;
  END;
  $$ LANGUAGE plpgsql IMMUTABLE;

  CREATE TRIGGER localization_insert_or_update
    BEFORE INSERT OR UPDATE ON executive.localization
    FOR EACH ROW
  EXECUTE PROCEDURE executive.localization_insert_or_update();

  CREATE TRIGGER generate_gid
    BEFORE INSERT OR UPDATE ON executive.localization
    FOR EACH ROW
  EXECUTE PROCEDURE public.generate_gid();

COMMIT;

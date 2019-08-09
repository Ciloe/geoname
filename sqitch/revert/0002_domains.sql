-- Revert geoname:0002_domains from pg

BEGIN;

  DROP FUNCTION IF EXISTS public.generate_gid CASCADE;
  DROP FUNCTION IF EXISTS public.gid_schema CASCADE;
  DROP FUNCTION IF EXISTS public.gid_table CASCADE;
  DROP FUNCTION IF EXISTS public.gid_entity_id CASCADE;
  DROP FUNCTION IF EXISTS public.tag3d_cat CASCADE;
  DROP FUNCTION IF EXISTS public.tag3d_sub_cat CASCADE;
  DROP FUNCTION IF EXISTS public.tag3d_tag CASCADE;
  DROP FUNCTION IF EXISTS public.tag3d CASCADE;
  DROP FUNCTION IF EXISTS public.get_filtered_tags CASCADE;
  DROP FUNCTION IF EXISTS public.get_first_filtered_tag CASCADE;

  DROP DOMAIN IF EXISTS public.global_id CASCADE;
  DROP DOMAIN IF EXISTS public.tag3d CASCADE;
  DROP DOMAIN IF EXISTS public.tag3d_array CASCADE;

  DROP FUNCTION IF EXISTS public.check_tag3d_array_validity CASCADE;

COMMIT;

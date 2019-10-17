<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportGeoNameCitiesCommand extends ImportGeoNameBaseCommand
{
    const REMOTE_ARCHIVE_COUNTRIES = 'http://download.geonames.org/export/dump/allCountries.zip';
    const REMOTE_ARCHIVE_FEATURE_CODE = 'http://download.geonames.org/export/dump/featureCodes_en.txt';
    const SQL_GEONAME_IMPORT = <<<SQL
DROP TABLE IF EXISTS geoname;
CREATE TABLE IF NOT EXISTS geoname (
  geonameid   int,
  name varchar(200),
  asciiname varchar(200),
  alternatenames text,
  latitude float,
  longitude float,
  fclass char(1),
  fcode varchar(10),
  country varchar(2),
  cc2 TEXT,
  admin1 varchar(20),
  admin2 varchar(80),
  admin3 varchar(20),
  admin4 varchar(20),
  population bigint,
  elevation int,
  gtopo30 int,
  timezone varchar(40),
  moddate date
);
CREATE INDEX geoname_idx ON geoname(geonameid);
CREATE INDEX geoname_country_idx ON geoname(country);
CREATE INDEX geoname_concat_idx ON geoname(fclass, fcode);
SQL;
    const SQL_FEATURE_CODE_IMPORT = <<<SQL
DROP TABLE IF EXISTS feature_code;

CREATE TEMPORARY TABLE IF NOT EXISTS feature_code (
  class char(1) NULL,
  code varchar(10) NULL,
  type text,
  PRIMARY KEY (class, code)
);

CREATE INDEX feature_code_idx ON feature_code(code);
CREATE INDEX feature_type_idx ON feature_code(type);
CREATE INDEX feature_concat_idx ON feature_code(class, code);

INSERT INTO feature_code
SELECT
  a[1],
  a[2],
  type
FROM (
  SELECT regexp_split_to_array(a[1], '\.'), a[2] AS type
  FROM (
    SELECT regexp_split_to_array(regexp_split_to_table(pg_read_file('/import/featureCodes_en.txt'), E'\\n+'), E'\\t+')
  ) AS dt(a)
) AS dt(a)
WHERE a[2] IS NOT NULL;
SQL;
    const SQL_INSERT_IMPORT = <<<SQL
DELETE FROM executive.localization WHERE 1 = 1;
INSERT INTO executive.localization (uuid, name, coordinates, "type", alt, tags, data, created_at)
SELECT
  uuid_generate_v4(),
  asciiname,
  ST_MakePoint(g.longitude, g.latitude),
  CASE
    WHEN fc.type IS NULL THEN 'Localization.Type.Unknown'
    ELSE concat(
      'Localization.Type.',
      CASE WHEN fc.class = 'U' THEN 'UnderWater' ELSE '' END,
      replace(initcap(replace(replace(regexp_replace(trim(fc.type), E'\\W+', '_', 'g'), '_s_', ''), '_', ' ')), ' ', '')
    )::tag3d
  END AS type,
  jsonb_build_object('geoname', g.geonameid),
  '{}'::tag3d_array,
  jsonb_build_object(
    'timezone', g.timezone,
    'population', COALESCE(g.population, 0),
    'codes', jsonb_build_array((g.admin1)::TEXT, (g.admin2)::TEXT, (g.admin3)::TEXT, (g.admin4)::TEXT)
    ),
  g.moddate::timestamp
FROM geoname g
LEFT JOIN feature_code fc ON g.fclass = fc.class AND g.fcode = fc.code;
SQL;

    protected static $defaultName = 'import:geo-name:cities';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Import all localization from geoName.')
            ->setHelp('This command allows you to import in database all 
            localization for all countries list in geoName file.')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->getUploader()->upload(self::REMOTE_ARCHIVE_COUNTRIES, 'allCountries.zip', $io);
        $this->getUploader()->upload(self::REMOTE_ARCHIVE_FEATURE_CODE, 'featureCodes_en.txt', $io);

        $connection = $this->getSession()->getConnection();
        $io->note('Create temporary table geoname');
        $connection->executeAnonymousQuery(self::SQL_GEONAME_IMPORT);

        $io->note('Create temporary table feature_code and copy data to table');
        $connection->executeAnonymousQuery(self::SQL_FEATURE_CODE_IMPORT);

        $io->note('Copy data to geoname table');
        $connection->executeAnonymousQuery("COPY geoname FROM '/import/allCountries.txt' NULL AS '';");

        $io->note('Import data in localization table');
        $connection->executeAnonymousQuery(self::SQL_INSERT_IMPORT);

        $io->success('Import geoname data finished.');
    }
}

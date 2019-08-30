<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportGeoNameCountryCommand extends ImportGeoNameBaseCommand
{
    const REMOTE_ARCHIVE_COUNTRY = 'http://download.geonames.org/export/dump/countryInfo.txt';
    const SQL_COUNTRY_IMPORT = <<<SQL
DROP TABLE IF EXISTS country_info;
CREATE TABLE IF NOT EXISTS country_info (
  iso_alpha2 char(2),
  iso_alpha3 char(3),
  iso_numeric integer,
  fips_code varchar(3),
  name varchar(200),
  capital varchar(200),
  areainsqkm double precision,
  population integer,
  continent varchar(2),
  tld varchar(10),
  currencycode varchar(3),
  currencyname varchar(20),
  phone varchar(20),
  postalcode varchar(100),
  postalcoderegex varchar(200),
  languages varchar(200),
  geonameId int,
  neighbors varchar(50),
  equivfipscode varchar(3)
);
CREATE INDEX country_info_idx ON country_info(geonameid);
COPY country_info FROM '/import/countryInfo.txt' NULL AS '';
SQL;

    protected static $defaultName = 'import:geo-name:country';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Import country info file from geoName.')
            ->setHelp('This command allows you to import in database all country info from geoName files.')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->getUploader()->upload(self::REMOTE_ARCHIVE_COUNTRY, 'countryInfo.txt', $io);

        $connection = $this->getSession()->getConnection();
        $io->note('Create temporary table country');
        $connection->executeAnonymousQuery(self::SQL_COUNTRY_IMPORT);

        $select = $connection->executeAnonymousQuery('SELECT * FROM country_info;');
        $count = $select->countRows();
        if ($count > 0) {
            $io->note(sprintf('Update %d localizations to add country info', $count));
            $io->progressStart($count);
            for ($i = 0; $i < $count; $i++) {
                $row = $select->fetchRow($i);
                $connection->executeAnonymousQuery(sprintf(
                    '
                    UPDATE executive.localization SET data = l.data, tags= l.tags
                    FROM (
                      SELECT
                        id,
                        executive.localization.data||jsonb_build_object(
                          \'currency\', jsonb_build_object(\'code\', ci.currencycode, \'name\', currencyname),
                          \'postal\', jsonb_build_object(\'code\', ci.postalcode, \'regexp\', ci.postalcoderegex),
                          \'iso\', jsonb_build_object(\'alpha2\', ci.iso_alpha2, \'alpha3\', ci.iso_alpha3, \'numeric\', ci.iso_numeric)
                        ) AS data,
                        executive.localization.tags||\'{Localization.SubType.Country}\'::tag3d_array AS tags
                      FROM executive.localization
                      INNER JOIN country_info ci ON ci.geonameid = (executive.localization.alt->>\'geoname\')::INT
                      WHERE geonameid = %d
                    ) l
                    WHERE executive.localization.id = l.id
                    ',
                    $row['geonameid']
                ));
                $io->progressAdvance();
            }
            $io->progressFinish();
        }

        $io->success('All localizations updated.');
    }
}

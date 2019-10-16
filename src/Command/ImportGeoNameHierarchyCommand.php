<?php

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportGeoNameHierarchyCommand extends ImportGeoNameBaseCommand
{
    const REMOTE_ARCHIVE_HIERARCHY = 'http://download.geonames.org/export/dump/hierarchy.zip';
    const SQL_HIERARCHY_IMPORT = <<<SQL
DROP TABLE IF EXISTS hierarchy;

CREATE TEMPORARY TABLE IF NOT EXISTS hierarchy (
    geonameid INT,
    childid INT,
    type TEXT
);

CREATE INDEX hierarchy_idx ON hierarchy(geonameid);
CREATE INDEX hierarchy_child_idx ON hierarchy(childid);
COPY hierarchy FROM '/import/hierarchy.txt' NULL AS '';
ALTER TABLE hierarchy ADD COLUMN id SERIAL;
SQL;

    protected static $defaultName = 'import:geo-name:hierarchy';

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Import hierarchy file from geoName.')
            ->setHelp('This command allows you to import in database all hierarchy from geoName files.')
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $this->getUploader()->upload(self::REMOTE_ARCHIVE_HIERARCHY, 'hierarchy.zip', $io);

        $connection = $this->getSession()->getConnection();
        $io->note('Create temporary table hierarchy');
        $connection->executeAnonymousQuery(self::SQL_HIERARCHY_IMPORT);

        $select = $connection->executeAnonymousQuery('SELECT * FROM hierarchy ORDER BY id ASC;');
        $count = $select->countRows();
        if ($count > 0) {
            $io->note(sprintf('Update %d localizations to add parents', $count));
            $io->progressStart($count);
            for ($i = 0; $i < $count; $i++) {
                $row = $select->fetchRow($i);
                $connection->executeAnonymousQuery(sprintf(
                    '
                    UPDATE executive.localization SET id_parent = (
                        SELECT id 
                        FROM executive.localization 
                        WHERE (alt->>\'geoname\')::INT = %d
                    ) 
                    WHERE (alt->>\'geoname\')::INT = %d;
                    ',
                    $row['geonameid'],
                    $row['childid']
                ));
                $io->progressAdvance();
            }
            $io->progressFinish();
        }

        $io->success('All localizations updated.');
    }
}

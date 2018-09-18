<?php

/*
 * This file is part of the package t3g/ddev-utilities.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace T3G\DdevUtilities\Command;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExportDatabaseCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this
            ->setDescription('DDEV database export')
            ->addArgument(
                'snapshot',
                InputArgument::OPTIONAL,
                'exports all files into a snapshot folder as well'
            );
    }

    /**
     * Executes the command for adding the lock file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        foreach ($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'] as $identifier => $connection) {
            $this->exportSchema($io, $connection, $identifier);
        }
        $isSnapshot = $input->getArgument('snapshot');
        if ($isSnapshot) {
            $snapshotDirectory = $_ENV['TYPO3_PATH_APP'] . '/data/snapshots/' . date('Y-m-d_h-i-s');
            // Create snapshot directory
            GeneralUtility::mkdir_deep($snapshotDirectory);
            // copy files from A to B
            $this->copySnapshotFiles($_ENV['TYPO3_PATH_APP'] . '/data/', $snapshotDirectory);
            $io->writeln('Copied snapshot of DBs to ' . $snapshotDirectory);
        }
        $io->success('Job done');
    }

    protected function exportSchema(SymfonyStyle $io, array $connectionConfiguration, string $identifier): bool
    {
        $io->writeln('Working on connection "' . $identifier . '"');
        if ($connectionConfiguration['host'] !== 'db') {
            $io->error('Skipping connection for ""');
            return false;
        }
        $dbName = $connectionConfiguration['dbname'];
        $outputFilename = $_ENV['TYPO3_PATH_APP'] . '/data/' . $dbName . '.sql';
        $io->writeln('Exporting "' . $dbName . '" from connection "' . $identifier . '"');

        $command = 'mysqldump ' . $dbName . ' --no-data --skip-add-locks --skip-disable-keys --skip-comments > ' . $outputFilename;

        $execOutput = [];
        exec($command, $execOutput);
        if (\count($execOutput) > 0) {
            foreach ($execOutput as $item) {
                $io->writeln($item);
                $io->error('Errors from CLI');
            }
        }
        $io->writeln('Exported DB structure');

        $ignoredTables = [
            'cache_md5params',
            'cache_treelist',
            'cf_cache_hash',
            'cf_cache_hash_tags',
            'cf_cache_imagesizes',
            'cf_cache_imagesizes_tags',
            'cf_cache_news_category',
            'cf_cache_news_category_tags',
            'cf_cache_pages',
            'cf_cache_pagesection',
            'cf_cache_pagesection_tags',
            'cf_cache_pages_tags',
            'cf_cache_rootline',
            'cf_cache_rootline_tags',
            'tx_realurl_uniqalias_cache_map',
            'be_sessions',
            'fe_sessions',
            'cf_extbase_object',
            'cf_extbase_object_tags',
            'cf_extbase_reflection',
            'cf_extbase_reflection_tags',
            'cf_extbase_datamapfactory_datamap_tags',
            'cf_extbase_datamapfactory_datamap'
        ];

        $command = 'mysqldump ' . $dbName . ' --skip-extended-insert --complete-insert --no-create-info --skip-comments --skip-add-locks --skip-disable-keys';

        foreach ($ignoredTables as $ignoredTable) {
            $command .= ' --ignore-table=db.' . $ignoredTable;
        }

        $command .= ' >> ' . $outputFilename;

        exec($command, $output);
        // Show CLI output if any
        if (\count($execOutput) > 0) {
            foreach ($execOutput as $item) {
                $io->writeln($item);
                $io->error('Errors from CLI');
            }
        }
        $io->writeln('Exported data to "' . $outputFilename . '"');
        return true;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @param       string $source Source path
     * @param       string $destination Destination path
     * @param       int $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    protected function copySnapshotFiles($source, $destination): bool
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $destination);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $destination);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry === '.' || $entry === '..' || substr($entry, 0, 1) !== '.') {
                continue;
            }

            // Deep copy directories
            $this->copySnapshotFiles($source . '/' . $entry, $destination . '/' . $entry);
        }

        // Clean up
        $dir->close();
        return true;
    }
}

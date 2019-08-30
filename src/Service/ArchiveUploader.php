<?php

namespace App\Service;

use Symfony\Component\Console\Style\SymfonyStyle;

class ArchiveUploader
{
    /**
     * @var string
     */
    private $projectDir;

    private $lastTransferred = 0;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @param string $remoteUrl
     * @param string $fileName
     * @param SymfonyStyle|null $io
     *
     * @return string
     */
    public function upload(string $remoteUrl, string $fileName, SymfonyStyle $io = null): string
    {
        $archiveDestPath = sprintf('%s/%s', $this->projectDir, 'import');
        $arrContextOptions = array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            )
        );

        $context = stream_context_create($arrContextOptions, [
            'notification' => function (
                $notification_code,
                $severity,
                $message,
                $message_code,
                $bytes_transferred,
                $bytes_max
            ) use ($io) {
                static $fileSize = null;

                switch ($notification_code) {
                    case STREAM_NOTIFY_RESOLVE:
                    case STREAM_NOTIFY_AUTH_REQUIRED:
                    case STREAM_NOTIFY_COMPLETED:
                    case STREAM_NOTIFY_FAILURE:
                    case STREAM_NOTIFY_AUTH_RESULT:
                        if (!is_null($io)) {
                            $io->error(sprintf('An error occurred : %s', $message));
                            $io->table(
                                ['type', 'value'],
                                [
                                    ['code', $notification_code],
                                    ['severity', $severity],
                                    ['message', $message],
                                    ['message_code', $message_code],
                                    ['transferred', $bytes_transferred],
                                    ['size', $bytes_max],
                                ]
                            );
                        }
                        break;

                    case STREAM_NOTIFY_REDIRECTED:
                        if (!is_null($io)) {
                            $io->note(sprintf('Redirect to : %s', $message));
                        }
                        break;

                    case STREAM_NOTIFY_CONNECT:
                        if (!is_null($io)) {
                            $io->note('Connected ...');
                        }
                        break;

                    case STREAM_NOTIFY_FILE_SIZE_IS:
                        $fileSize = $bytes_max/1024;
                        if (!is_null($io)) {
                            $io->note('Downloading ...');
                            $io->progressStart($fileSize);
                        }
                        break;

                    case STREAM_NOTIFY_PROGRESS:
                        if ($bytes_transferred > 0) {
                            if (!isset($fileSize)) {
                                printf("\rTaille du fichier inconnue.. %2d kb done..", $bytes_transferred/1024);
                            } else {
                                $transferred = $bytes_transferred/1024 - $this->lastTransferred;
                                $this->lastTransferred = $bytes_transferred/1024;

                                if (!is_null($io)) {
                                    $io->progressAdvance($transferred);
                                }
                            }
                        }
                        break;
                }
            }
        ]);

        $streamContent = file_get_contents($remoteUrl,FILE_USE_INCLUDE_PATH, $context);
        if (!is_null($io)) {
            $io->progressFinish();
        }

        if ($streamContent === false) {
            if (!is_null($io)) {
                $io->error('An error occurred : can\'t upload file');
            }
            die;
        }

        $destinationPath = sprintf('%s/%s', $archiveDestPath, $fileName);
        $io->note(sprintf('Archive copying on %s', $destinationPath));
        file_put_contents($destinationPath, $streamContent);

        if (strstr($destinationPath, '.zip') !== FALSE) {
            $zip = new \ZipArchive();
            $res = $zip->open($destinationPath);
            $destinationPath = str_replace('.zip', '.txt', $destinationPath);
            if ($res === TRUE) {
                $zip->extractTo($archiveDestPath);
                $zip->close();
            } else {
                if (!is_null($io)) {
                    $io->error('An error occurred : can\'t unzip file');
                }
                die;
            }
        }

        return $destinationPath;
    }
}

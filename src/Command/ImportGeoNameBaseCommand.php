<?php

namespace App\Command;

use App\Service\ArchiveUploader;
use PommProject\ModelManager\Session;
use Symfony\Component\Console\Command\Command;

class ImportGeoNameBaseCommand extends Command
{
    protected static $defaultName = 'import:geo-name:all';

    /**
     * @var ArchiveUploader
     */
    private $uploader;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param ArchiveUploader $uploader
     * @param Session $pommSession
     */
    public function __construct(ArchiveUploader $uploader, Session $pommSession)
    {
        parent::__construct(null);

        $this->uploader = $uploader;
        $this->session = $pommSession;
    }

    /**
     * @return ArchiveUploader
     */
    public function getUploader(): ArchiveUploader
    {
        return $this->uploader;
    }

    /**
     * @param ArchiveUploader $uploader
     *
     * @return ImportGeoNameBaseCommand
     */
    public function setUploader(ArchiveUploader $uploader): self
    {
        $this->uploader = $uploader;

        return $this;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @param Session $session
     *
     * @return ImportGeoNameBaseCommand
     */
    public function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }
}

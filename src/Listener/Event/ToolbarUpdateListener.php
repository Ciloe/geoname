<?php

namespace App\Listener\Event;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ToolbarUpdateListener
{
    /**
     * @var string
     */
    private $env;

    /**
     * @param string $env
     */
    public function __construct(string $env)
    {
        $this->env = $env;
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!in_array($this->env, ['dev', 'test'])) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
    }
}

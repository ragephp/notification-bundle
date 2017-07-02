<?php
namespace RagePHP\RageEmailBundle\Event;

use RagePHP\RageEmailBundle\Message\Message;
use Symfony\Component\EventDispatcher\Event;

class SendEvent extends Event
{
    protected $message;
    protected $server;
    protected $eximId;

    public function __construct(Message $message, $server = null, $eximId = null)
    {
        $this->message = $message;
        $this->server = $server;
        $this->eximId = $eximId;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getEximId()
    {
        return $this->eximId;
    }
}
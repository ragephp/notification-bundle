<?php
namespace RagePHP\RageEmailBundle\Event;

use RagePHP\RageEmailBundle\Message\Message;
use Symfony\Component\EventDispatcher\Event;

class RenderEvent extends Event
{
    protected $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
<?php
namespace RagePHP\RageEmailBundle\Swift;

use Swift_Events_EventDispatcher;
use Swift_Mime_Message;
use Swift_Transport_EsmtpTransport;
use Swift_Transport_IoBuffer;

class SmtpTransport extends Swift_Transport_EsmtpTransport
{
    protected $hosts;
    protected $lastEximId;

    public function __construct(Swift_Transport_IoBuffer $buf, $extensionHandlers, Swift_Events_EventDispatcher $dispatcher)
    {
        parent::__construct($buf, $extensionHandlers, $dispatcher);
        $this->registerPlugin(new EximIdPlugin());
    }

    public function setHost($host)
    {
        if (is_array($host)) {
            $this->setHosts($host);
        }
        return parent::setHost($host);
    }

    protected function setHosts($hosts)
    {
        $this->hosts = $hosts;
        $this->selectHost();
    }

    protected function selectHost()
    {
        if ($this->hosts) {
            $this->setHost($this->hosts[array_rand($this->hosts)]);
        }
    }

    public function stop()
    {
        parent::stop();
        $this->selectHost();
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->lastEximId = null;
        $result = parent::send($message, $failedRecipients);
        return $result;
    }

    public function setLastEximId($id)
    {
        $this->lastEximId = $id;
    }

    public function getLastEximId()
    {
        return $this->lastEximId;
    }
}

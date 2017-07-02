<?php
namespace RagePHP\RageEmailBundle\Swift;

use Swift_Events_ResponseEvent;
use Swift_Events_ResponseListener;

class EximIdPlugin implements Swift_Events_ResponseListener
{
    /**
     * {@inheritdoc}
     */
    public function responseReceived(Swift_Events_ResponseEvent $evt)
    {
        if (strpos($evt->getResponse(), 'OK') !== false) {
            preg_match('/id=(.{6}\-.{6}\-.{2})/', $evt->getResponse(), $match);
            if ($match) {
                $evt->getSource()->setLastEximId($match[1]);
            }
        }
    }
}
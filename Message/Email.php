<?php
namespace RageNotificationBundle\Message;

use Swift_Image;
use Swift_Message;
use Symfony\Bridge\Twig\TwigEngine;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Class Email
 */
class Email
{
    protected $mailer;
    protected $twigEngine;
    protected $from;
    protected $to;
    protected $replyTo;
    protected $templateId;
    protected $templateVars;
    protected $embedImages;
    protected $templatePath = 'email';
    protected $cssFile;

    private $subject;
    private $txtMessage;
    private $htmlMessage;

    /**
     * Email constructor.
     * @param \Swift_Mailer $mailer
     * @param TwigEngine    $twigEngine
     */
    public function __construct(\Swift_Mailer $mailer, TwigEngine $twigEngine)
    {
        $this->mailer = $mailer;
        $this->twigEngine = $twigEngine;
    }

    /**
     * @return Swift_Message
     */
    public function getMessage()
    {
        /* @var $message \Swift_Message */
        $message = $this->mailer->createMessage()
            ->setFrom($this->from)
            ->setReplyTo($this->replyTo)
            ->setTo($this->to)
            ->setSubject($this->getRenderedSubject());
        $message->setBody($this->getEmbeddedHtmlMessage($message), 'text/html');
        $message->addPart($this->getRenderedTxtMessage(), 'text/plain');

        return $message;
    }

    /**
     * @return int
     */
    public function sendMessage()
    {
        return $this->mailer->send($this->getMessage());
    }

    /**
     * @return string
     */
    public function getRenderedSubject()
    {
        if (empty($this->subject)) {
            $template = $this->templatePath.'/'.$this->templateId.'/subject.txt.twig';
            $this->subject = $this->render($template, $this->templateVars);
        }

        return $this->subject;
    }

    /**
     * @return string
     */
    public function getRenderedTxtMessage()
    {
        if (empty($this->txtMessage)) {
            $template = $this->templatePath.'/'.$this->templateId.'/email.txt.twig';
            $this->txtMessage = $this->render($template, $this->templateVars);
        }

        return $this->txtMessage;
    }

    /**
     * @return string
     * @throws \TijsVerkoyen\CssToInlineStyles\Exception
     */
    public function getRenderedHtmlMessage()
    {
        if (empty($this->htmlMessage)) {
            $template = $this->templatePath.'/'.$this->templateId.'/email.html.twig';
            if (!empty($this->cssFile)) {
                $cssToInlineStyles = new CssToInlineStyles();
                $cssToInlineStyles->setHTML($this->render($template, $this->templateVars));
                $cssToInlineStyles->setCSS(file_get_contents($this->cssFile));
                $this->htmlMessage = $cssToInlineStyles->convert();
            } else {
                $this->htmlMessage = $this->render($template, $this->templateVars);
            }
        }

        return $this->htmlMessage;
    }

    /**
     * @param Swift_Message $message
     * @return mixed|string
     */
    public function getEmbeddedHtmlMessage(\Swift_Message &$message)
    {
        $renderedHtml = $this->getRenderedHtmlMessage();
        if (!empty($this->embedImages['urlPrefix'])) {
            $regexp = '#[\'"]('.preg_quote($this->embedImages['urlPrefix']).'([^\'"]+\.(gif|png|jpg|jpeg)?))[\'"]#ium';
            preg_match_all($regexp, $renderedHtml, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $embed = $message->embed(Swift_Image::fromPath($this->embedImages['path'].$match[2]));
                $renderedHtml = str_replace($match[1], $embed, $renderedHtml);
            }
        }

        return $renderedHtml;
    }

    /**
     * @param mixed $from
     * @return Email
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param mixed $to
     * @return Email
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @param mixed $replyTo
     * @return Email
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @param mixed $id
     * @param mixed $vars
     * @return Email
     */
    public function setTemplate($id, $vars)
    {
        $this->templateId = $id;
        $this->templateVars = $vars;

        return $this;
    }

    /**
     * @param mixed $urlPrefix
     * @param mixed $path
     * @return Email
     */
    public function setEmbedImages($urlPrefix, $path)
    {
        $this->embedImages = [
            'urlPrefix' => $urlPrefix,
            'path' => $path,
        ];

        return $this;
    }

    /**
     * @param mixed $path
     * @return Email
     */
    public function setTemplatePath($path)
    {
        $this->templatePath = $path;

        return $this;
    }

    /**
     * @param mixed $file
     * @return Email
     */
    public function setCssFile($file)
    {
        $this->cssFile = $file;

        return $this;
    }

    protected function render($template, $vars)
    {
        $vars['utm_params'] = 'utm_source=email&utm_medium=transaction&utm_campaign='.$this->templateId;

        return $this->twigEngine->render($template, $vars);
    }
}

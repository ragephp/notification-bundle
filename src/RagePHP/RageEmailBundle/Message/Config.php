<?php
namespace RagePHP\RageEmailBundle\Message;

use InvalidArgumentException;
use RagePHP\RageEmailBundle\Exception\TemplateNotFoundException;
use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser;
use Symfony\Component\Templating\EngineInterface;

class Config
{
    /** @var EngineInterface */
    protected $tplEngine;
    /** @var TemplateLocator */
    protected $tplLocator;
    /** @var TemplateNameParser */
    protected $tplNameParser;

    protected $cachePath;
    protected $templatePath;
    protected $cssFile;
    protected $cacheInlinedCSS = false;
    protected $domain;
    protected $from;
    protected $replyTo;
    protected $embedImages = false;
    
    public function setTemplateEngine(EngineInterface $engine) { $this->tplEngine = $engine; }
    public function setTemplateLocator(TemplateLocator $locator) { $this->tplLocator = $locator; }
    public function setTemplateNameParser(TemplateNameParser $nameParser) { $this->tplNameParser = $nameParser; }
    public function setTemplatePath($templatePath) { $this->templatePath = $templatePath; }
    public function setCacheInlinedCSS($value) { $this->cacheInlinedCSS = $value; }
    public function setCachePath($cachePath) { $this->cachePath = $cachePath; }
    public function setCssFile($value) { $this->cssFile = $value; }
    public function getCssFile() { return $this->cssFile; }
    public function setFrom($val) { $this->from = $val; }
    public function getFrom() { return $this->from; }
    public function setReplyTo($val) { $this->replyTo = $val; }
    public function getReplyTo() { return $this->replyTo; }
    public function setDomain($val) { $this->domain = $val; }
    public function getDomain() { return $this->domain; }
    public function setEmbedImages($urlPrefix, $path)
    {
        $this->embedImages = [ 'urlPrefix' => $urlPrefix, 'path' => $path ];
    }
    public function getEmbedImages() { return $this->embedImages; }

    public function getSubjectTemplatePath(Message $message)
    {
        return $this->templatePath . '/' . $message->getTemplate() . '/subject.txt.twig';
    }

    public function getPlainTextBodyTemplatePath(Message $message)
    {
        $template = $this->templatePath . '/' . $message->getTemplate() . '/email.txt.twig';
        return $this->tplEngine->exists($template) ? $template : null;
    }

    public function getHtmlBodyTemplatePath(Message $message)
    {
        return $this->templatePath . '/' . $message->getTemplate() . '/email.html.twig';
    }

    public function getCachedHtmlBodyTemplatePath(Message $message)
    {
        if ($this->cacheInlinedCSS && $this->cssFile) {
            $realPath = realpath($this->cachePath);
            return $this->cachePath . '/' . md5($realPath . ':' . $message->getTemplate()) . '.html.twig';
        } else {
            return null;
        }
    }

    public function getTemplate($template)
    {
        return file_get_contents($this->tplLocator->locate($this->tplNameParser->parse($template)));
    }

    public function render($template, $vars)
    {
        try {
            return $this->tplEngine->render($template, $vars);
        } catch (InvalidArgumentException $e) {
            throw new TemplateNotFoundException($template, 0, $e);
        }
    }
}
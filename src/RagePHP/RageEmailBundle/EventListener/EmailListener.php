<?php
namespace RagePHP\RageEmailBundle\EventListener;

use Gedmo\Translatable\TranslatableListener;
use RagePHP\RageEmailBundle\Event\RenderEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\TranslatorInterface;

class EmailListener implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $oldLocaleState = null;
    protected $localeConfig = [ ];

    /* @var RequestContext $requestContext */
    protected $requestContext;
    /* @var TranslatorInterface $translator */
    protected $translator;
    /* @var TranslatableListener $gedmoTranslatable */
    protected $gedmoTranslatable;

    public function __construct(RequestContext $requestContext, TranslatorInterface $translator, TranslatableListener $gedmoTranslatable = null)
    {
        $this->requestContext = $requestContext;
        $this->translator = $translator;
        $this->gedmoTranslatable = $gedmoTranslatable;
    }

    public function setLocaleConfig($config)
    {
        $this->localeConfig = $config;
    }

    public function onBeforeRenderHTML(RenderEvent $event)
    {
        $locale = $event->getMessage()->getLocale();
        $this->oldLocaleState = [
            'locale' => $this->translator->getLocale(),
            'scheme' => $this->requestContext->getScheme(),
            'host' => $this->requestContext->getHost(),
            'httpPort' => $this->requestContext->getHttpPort(),
            'httpsPort' => $this->requestContext->getHttpsPort(),
        ];
        $this->applyConfig($this->localeConfig[$locale]);
    }

    public function onAfterRenderHTML(RenderEvent $event)
    {
        $old = $this->oldLocaleState;
        if (!empty($old)) $this->applyConfig($old);
        $this->oldLocaleState = null;
    }

    protected function applyConfig($config)
    {
        $this->requestContext
            ->setScheme($config['scheme'])->setHost($config['host'])
            ->setHttpPort($config['httpPort'])->setHttpsPort($config['httpsPort']);
        $this->translator->setLocale($config['locale']);
        if (!empty($this->gedmoTranslatable)) {
            $this->gedmoTranslatable->setTranslatableLocale($config['locale']);
        }
    }
}
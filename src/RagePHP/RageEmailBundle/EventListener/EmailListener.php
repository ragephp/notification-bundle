<?php
namespace RagePHP\RageEmailBundle\EventListener;

use RagePHP\RageEmailBundle\Event\RenderEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class EmailListener implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $oldLocaleState = null;
    protected $localeConfig = [ ];

    public function setLocaleConfig($config)
    {
        $this->localeConfig = $config;
    }

    public function onBeforeRenderHTML(RenderEvent $event)
    {
        $locale = $event->getMessage()->getLocale();
        $this->oldLocaleState = [
            'locale' => $this->container->get('translator')->getLocale(),
            'scheme' => $this->container->get('router.request_context')->getScheme(),
            'host' => $this->container->get('router.request_context')->getHost(),
            'httpPort' => $this->container->get('router.request_context')->getHttpPort(),
            'httpsPort' => $this->container->get('router.request_context')->getHttpsPort(),
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
        $this->container->get('router.request_context')
            ->setScheme($config['scheme'])->setHost($config['host'])
            ->setHttpPort($config['httpPort'])->setHttpsPort($config['httpsPort']);
        $this->container->get('translator')->setLocale($config['locale']);
        $this->container->get('stof_doctrine_extensions.listener.translatable')->setTranslatableLocale($config['locale']);
    }
}
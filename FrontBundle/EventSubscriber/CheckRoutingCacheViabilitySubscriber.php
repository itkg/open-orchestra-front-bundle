<?php

namespace OpenOrchestra\FrontBundle\EventSubscriber;

use OpenOrchestra\ModelInterface\Model\ReadNodeInterface;
use OpenOrchestra\ModelInterface\Repository\ReadNodeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;

/**
 * Class CheckRoutingCacheViabilitySubscriber
 */
class CheckRoutingCacheViabilitySubscriber implements EventSubscriberInterface
{
    protected $router;
    protected $nodeRepository;
    protected $lastPublishedNode;

    /**
     * @param Router                      $router
     * @param ReadNodeRepositoryInterface $nodeRepository
     */
    public function __construct(Router $router, ReadNodeRepositoryInterface $nodeRepository)
    {
        $this->router = $router;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * Test if the file cache is up to date
     *
     * @param GetResponseEvent $event
     */
    public function checkCacheFileAndRefresh(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $cacheDir = $this->router->getOption('cache_dir');

        $matcherCacheClass = $cacheDir . '/' . $this->router->getOption('matcher_cache_class') . '.php';
        $this->testCacheFile($matcherCacheClass);

        $generatorCacheClass = $cacheDir . '/' . $this->router->getOption('generator_cache_class') . '.php';
        $this->testCacheFile($generatorCacheClass);
    }

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'checkCacheFileAndRefresh',
        );
    }

    /**
     * @param string  $cacheClass
     *
     * @return bool
     */
    protected function testCacheFile($cacheClass)
    {
        if (file_exists($cacheClass)) {
            $cacheAge = filemtime($cacheClass);
            $lastPublishedNode = $this->getLastNodePublished();
            if ($lastPublishedNode instanceof ReadNodeInterface && $lastPublishedNode->getUpdatedAt() instanceof \DateTime && $cacheAge < $lastPublishedNode->getUpdatedAt()->getTimestamp()) {
                unlink($cacheClass);

                return true;
            }
        }

        return false;
    }

    /**
     * @return ReadNodeInterface
     */
    protected function getLastNodePublished()
    {
        if (!$this->lastPublishedNode instanceof ReadNodeInterface) {
            $this->lastPublishedNode = $this->nodeRepository->findLastPublished();
        }

        return $this->lastPublishedNode;
    }
}

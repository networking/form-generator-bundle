<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Twig\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class NetworkingHelperExtension.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class FormHelperExtension extends AbstractExtension
{
    /**
     * Container.
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var bool
     */
    protected $captureLock = false;

    /**
     * @var array
     */
    protected $collectedHtml = [];

    /**
     * @var bool
     */
    protected $ckeditorRendered = false;

    /**
     * @var ArrayCollection
     */
    protected $formBlocks;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param string $pageClass
     * @param string $pageContentClass
     */
    public function __construct(
        Pool $pool,
        ManagerRegistry $managerRegistry,
        protected $pageClass,
        protected $pageContentClass
    ) {
        $this->pool = $pool;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'networking_form_generator.helper.twig';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'get_form_page_links',
                $this->getPageLinks(...),
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @return array
     */
    public function getPageLinks($formId)
    {
        $content = $this->managerRegistry->getRepository(
            $this->pageContentClass
        )->findBy(
            ['form' => $formId]
        );

        $links = [];
        $pageAdmin = $this->pool->getAdminByClass($this->pageClass);

        /** @var LayoutBlock $block */
        foreach ($content as $block) {
            $draftRoute = $pageAdmin->getRouteGenerator()->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $block->getPage()
                        ->getRoute(),
                ]
            );
            $url = $pageAdmin->getRouteGenerator()->generate(
                'networking_init_view_draft',
                [
                    'locale' => $block->getPage()->getLocale(),
                    'path' => base64_encode($draftRoute),
                ]
            );
            $links[] = [
                'url' => $url,
                'title' => $block->getPage()->getAdminTitle(),
            ];
        }

        return $links;
    }
}

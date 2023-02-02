<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Sonata\AdminBundle\Admin\Pool;
use Doctrine\Persistence\ManagerRegistry;
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
     * @var string
     */
    protected $pageClass;

    /**
     * @var string
     */
    protected $pageContentClass;

    public function __construct(Pool $pool, ManagerRegistry $managerRegistry, $pageClass, $pageContentClass){
        $this->pool = $pool;
        $this->managerRegistry = $managerRegistry;
        $this->pageClass = $pageClass;
        $this->pageContentClass = $pageContentClass;
    }


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
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
            new TwigFunction('get_form_page_links', [$this, 'getPageLinks'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param $formId
     *
     * @return array
     */
    public function getPageLinks($formId)
    {
        $content = $this->managerRegistry->getRepository($this->pageContentClass)->findBy(
            ['form' => $formId]
        );

        $blocks = $this->getFormPageContentLayoutBlocks();

        $filteredBlocks = $blocks->filter(function (LayoutBlock $block) use ($content) {
            foreach ($content as $item) {
                if ($item->getId() == $block->getObjectId()) {
                    return true;
                }
            }
        });

        $links = [];
        $pageAdmin = $this->pool->getAdminByClass($this->pageClass);

        /** @var LayoutBlock $block */
        foreach ($filteredBlocks as $block) {
            $url = $pageAdmin->generateUrl('show', ['id' => $block->getPageId()]);
            $links[] = ['url' => $url, 'title' => $block->getPage()->getAdminTitle()];
        }

        return $links;
    }

    protected function getFormPageContentLayoutBlocks()
    {
        if (!$this->formBlocks) {
            $blocks = $this->managerRegistry->getRepository(LayoutBlock::class)->findBy(
                ['classType' => $this->pageContentClass]
            );

            $this->formBlocks = new ArrayCollection($blocks);
        }

        return $this->formBlocks;
    }
}

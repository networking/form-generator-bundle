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
use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Twig\TokenParser\JSTokenParser;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormView;

/**
 * Class NetworkingHelperExtension
 * @package Networking\InitCmsBundle\Twig\Extension
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class FormHelperExtension extends \Twig_Extension implements ContainerAwareInterface
{
    /**
     * Container
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
    protected $collectedHtml = array();

    /**
     * @var bool
     */
    protected $ckeditorRendered = false;


    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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
        return array(
            new \Twig_SimpleFunction('get_form_page_links', array($this, 'getPageLinks'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @param $formId
     * @return array
     */
    public function getPageLinks($formId)
    {
        $blocks = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:LayoutBlock')->findBy(
            array('classType' => 'Networking\\FormGeneratorBundle\\Entity\\FormPageContent', 'objectId' => $formId)
        );

        $links = array();
        $pageClass = $this->container->getParameter('networking_init_cms.admin.page.class');
        $pageAdmin = $this->container->get('sonata.admin.pool')->getAdminByClass($pageClass);

        /** @var LayoutBlock $block */
        foreach ($blocks as $block){

            $url = $pageAdmin->generateUrl('show', array('id' => $block->getPageId()));
            $links[] = array('url' => $url, 'title' => $block->getPage()->getAdminTitle());
        }

        return $links;

    }


    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        $db_driver = $this->getParameter('networking_init_cms.db_driver');

        switch ($db_driver) {
            case 'orm':
                return $this->getService('doctrine');
                break;
            case 'mongodb':
                return $this->getService('doctrine_mongodb');
                break;
            default:
                throw new \LogicException('cannot find doctrine for db_driver');
                break;
        }
    }

    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     *
     * @return object The associated service
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * Get parameters from the service container
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

}

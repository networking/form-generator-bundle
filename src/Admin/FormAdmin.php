<?php
/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Admin;

use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class FormAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/forms';

    /**
     * @var string
     */
    protected $baseRouteName = 'admin_networking_forms';

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-file';
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add(
            'excelExport',
            'form-excel-export/{id}',
            ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:excelExport'])
            ->add(
                'deleteFormEntry',
                'delete-form-entry/{id}/entry/{rowid}',
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:deleteFormEntry'])
            ->add(
                'matchFormEntry',
                'match-form-entry/{id}/entry/{rowid}' ,
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:matchFormEntry'])
            ->add(
                'addMatch',
                'add-match/{id}/entry/{rowid}/addressId/{addressid}' ,
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:addMatch'])
            ->add(
                'addNewAddress',
                'add-address/{id}/entry/{rowid}' ,
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:addAddress'])
            ->add(
                'deleteAllFormEntry',
                'delete-all-form-entry/{id}',
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:deleteAllFormEntry'])
            ->add(
                'copy',
                'copy/{id}',
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:copy'])
            ->add(
                'addressConfig',
                'address_config/{id}' ,
                ['_controller' => 'NetworkingFormGeneratorBundle:FormAdmin:addressConfig'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('name');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);
        $listMapper->add('pages', 'string', ['template' => '@NetworkingFormGenerator/Admin/pages.html.twig']);
        $listMapper->add(
            '_action',
            'actions',
            [
                'label' => false,
                'actions' => [
                    'edit' => [],
                    'show' => [],
                    'copy' => [
                        'template' => '@NetworkingFormGenerator/Admin/list_action_copy.html.twig',
                    ],
                    'address' => [
                        'template' => 'NetworkingFormGeneratorBundle:Admin:addressConfigButton.html.twig',
                    ],
                    'delete' => [],
                ],
            ]
        );
    }
}

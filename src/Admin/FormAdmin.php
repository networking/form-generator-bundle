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
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            ['_controller' => 'Networking\FormGeneratorBundle\Controller\FormAdminController::excelExportAction']
        )
            ->add(
                'deleteFormEntry',
                'delete-form-entry/{id}/entry/{rowid}',
                ['_controller' => 'Networking\FormGeneratorBundle\Controller\FormAdminController::deleteFormEntryAction']
            )
            ->add(
                'deleteAllFormEntry',
                'delete-all-form-entry/{id}',
                ['_controller' => 'Networking\FormGeneratorBundle\Controller\FormAdminController::deleteAllFormEntryAction']
            )
            ->add(
                'copy',
                'copy/{id}',
                ['_controller' => 'Networking\FormGeneratorBundle\Controller\FormAdminController::copyAction']
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'name',
                null,
                [],
                null,
                ['translation_domain' => 'formGenerator']
            )->add(
            'online',
            CallbackFilter::class,
            [
                'callback' => [
                    $this,
                    'getAllOnline',
                ],
            ],
            ChoiceType::class,
            [
                'placeholder' => 'filter.choice.all',
                'choices' => [
                    'filter.choice.online'=> 1,
                    'filter.choice.offline' => 0,
                ],
                'translation_domain' => 'formGenerator'
            ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('name');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);
        $listMapper->addIdentifier('name');
        $listMapper->add('pages', 'string', ['template' => '@NetworkingFormGenerator/Admin/pages.html.twig']);
        $listMapper->add('online', 'boolean', ['editable' => true]);
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
                    'delete' => [],
                ],
            ]
        );
    }

    /**
     * Returns a list of default filters.
     */
    protected function configureDefaultFilterValues(array &$filterValues)
    {

        $filterValues['online'] = ['value' => 1];
    }

    /**
     * @param ProxyQuery $ProxyQuery
     * @param $alias
     * @param $field
     * @param $data
     *
     * @return bool
     */
    public function getAllOnline(ProxyQuery $ProxyQuery, $alias, $field, $data)
    {
        $active = true;
        $value = $data['value'];

        $qb = $ProxyQuery->getQueryBuilder();

        if ($value === 1) {
            $qb->andWhere(sprintf('%s.%s IS NULL', $alias, $field));
            $qb->orWhere(sprintf('%s.%s = 1', $alias, $field));
        }

        if($value === 0){
            $qb->andWhere(sprintf('%s.%s = 0', $alias, $field));
        }


        return $active;
    }
}

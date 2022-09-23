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

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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

    protected function configureRoutes(RouteCollectionInterface $collection): void
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
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add(
                'name',
                null,
                [],
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
                [
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'placeholder' => 'filter.choice.all',
                        'choices' => [
                            'filter.choice.online' => 1,
                            'filter.choice.offline' => 0,
                        ],
                        'translation_domain' => 'formGenerator',
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add(
                'name',
                TextType::class,
                ['layout' => 'horizontal', 'attr' => ['class' => 'input-xlarge']]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'required' => false,
                    'layout' => 'horizontal',
                    'attr' => ['class' => 'input-xlarge'],
                    'help_block' => 'form.help.comma_separated',
                ]
            )
            ->add('action', ChoiceType::class, [
                'layout' => 'horizontal',
                'attr' => ['class' => 'input-xlarge'],
                'choices' => [
                    'Email' => 'email',
                    'DB' => 'db',
                    'Email & DB' => 'email_db',
                ],
                'choice_translation_domain' => false
            ])
            ->add('redirect', TextType::class, [
                'required' => false,
                'layout' => 'horizontal',
                'attr' => ['class' => 'input-xlarge'],
            ])
            ->add('infoText', CKEditorType::class, ['widget_form_group_attr' => ['class' => 'col-md-12']])
            ->add('thankYouText', CKEditorType::class, ['widget_form_group_attr' => ['class' => 'col-md-12'], 'autoload' => false]);
    }

    protected function configureListFields(ListMapper $listMapper): void
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
    protected function configureDefaultFilterValues(array &$filterValues): void
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

        if ($value === 0) {
            $qb->andWhere(sprintf('%s.%s = 0', $alias, $field));
        }


        return $active;
    }
}

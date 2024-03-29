<?php

declare(strict_types=1);

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


    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/forms';
    }

    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_networking_forms';
    }

    public function getIcon(): string
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
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'name',
                null,
                [
                    'field_options' => [

                        'row_attr' => ['class' => 'form-floating'],
                    ],
                ],
                ['translation_domain' => 'formGenerator']
            )->add(
                'online',
                CallbackFilter::class,
                [
                    'callback' => $this->getAllOnline(...),
                    'field_options' => [

                        'row_attr' => ['class' => 'form-floating'],
                    ],
                ],
                [
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'placeholder' => 'filter.choice.all',
                        'choices' => [
                            'filter.choice.online' => 1,
                            'filter.choice.offline' => 0,
                        ],
                        'translation_domain' => 'formGenerator',
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('main', ['label' => false])
            ->add(
                'name',
                TextType::class,
                [
                    'row_attr' => ['class' => 'form-floating mb-3'],
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'required' => false,
                    'row_attr' => ['class' => 'form-floating mb-3'],
                    'help_block' => 'form.help.comma_separated',
                ]
            )
            ->add('action', ChoiceType::class, [
                'layout' => 'horizontal',
                'row_attr' => ['class' => 'form-floating mb-3'],
                'choices' => [
                    'Email' => 'email',
                    'DB' => 'db',
                    'Email & DB' => 'email_db',
                ],
                'choice_translation_domain' => false,
            ])
            ->add('redirect', TextType::class, [
                'required' => false,
                'layout' => 'horizontal',
                'row_attr' => ['class' => 'form-floating mb-3'],
            ])
            ->add(
                'infoText',
                CKEditorType::class,
                [
                ]
            )
            ->add(
                'thankYouText',
                CKEditorType::class,
                [
                    'autoload' => false,
                ]
            );
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('name');
        $list->add('pages', 'string', [
            'virtual_field' => true,
            'template' => '@NetworkingFormGenerator/Admin/pages.html.twig',
        ]);
        $list->add('online', 'boolean', ['editable' => true]);
        $list->add(
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
     * @param $alias
     * @param $field
     * @param $data
     *
     * @return bool
     */
    public function getAllOnline(ProxyQuery $ProxyQuery, $alias, $field, $data)
    {
        $active = true;
        $value = $data->getValue();

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

    public function configureFormOptions(array &$formOptions): void
    {

        if ($this->getSubject()?->getId()) {
            $formOptions['method'] = 'PUT';
        }
        parent::configureFormOptions(
            $formOptions
        ); // TODO: Change the autogenerated stub
    }
}

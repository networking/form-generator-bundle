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

use Networking\InitCmsBundle\Admin\BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class FormFieldAdmin extends BaseAdmin
{

    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'cms/form_fields';
    }


    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'admin_networking_form_fields';
    }

    public function getIcon(): string
    {
        return 'glyphicon-file';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('name')
            ->add('fieldLabel')
            ->add('type',
                'choice',
                ['choices' => ['text' => 'text', 'textarea' => 'textarea', 'select' => 'select', 'radio' => 'radio', 'checkboxes' => 'checkboxes']]
            )
        ;
    }
}

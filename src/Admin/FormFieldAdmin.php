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
use Sonata\AdminBundle\Form\FormMapper;

class FormFieldAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/form_fields';

    /**
     * @var string
     */
    protected $baseRouteName = 'admin_networking_form_fields';

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-file';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('name')
            ->add('fieldLabel')
            ->add('type',
                'choice',
                array(
                    'choices' => array(
                        'text' => 'text',
                        'textarea' => 'textarea',
                        'select' => 'select',
                        'radio' => 'radio',
                        'checkboxes' => 'checkboxes',
                    ),
                )
            )
        ;
    }
}

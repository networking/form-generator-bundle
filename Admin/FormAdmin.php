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


    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form)
    {
        $form->add('name');
    }

    protected function configureListFields(ListMapper $listMapper){
        parent::configureListFields($listMapper);
        $listMapper->add(
            '_action',
            'actions',
            array(
                'label' => ' ',
                'actions' => array(
                    'edit' => array(),
                    'show' => array(),
                    'delete' => array(),
                )
            )
        );
    }


} 
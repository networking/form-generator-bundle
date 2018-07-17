<?php
/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Model\ContentInterface;
use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;
use Symfony\Component\Form\FormBuilder;

/**
 * FormPageContent.
 *
 * @ORM\Table(name="form_page_content")
 * @ORM\Entity
 */
class FormPageContent implements ContentInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Form
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Entity\Form",cascade={"merge"})
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $form;

    /**
     * @return mixed
     */
    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @Sonata\FormCallback
     */
    public static function configureFormFields(FormBuilder $formBuilder)
    {
        $formBuilder->add(
            'form',
            AutocompleteType::class,
               [
                   'label' => 'form.label.form',
                   'translation_domain' => 'formGenerator',
                   'class' => Form::class,
                   'attr' => ['style' => 'width: 220px;'],
                   'layout' => 'horizontal',
               ]
        );
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return $this
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getTemplateOptions($params = [])
    {
        return [
            'form_page_content' => $this->form,
            'params' => $params,
        ];
    }

    /**
     * @return array
     */
    public function getAdminContent()
    {
        return [
            'content' => ['form_page_content' => $this->form],
            'template' => '@NetworkingFormGenerator/Admin/formPageContent.html.twig',
        ];
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Custom Form';
    }
}

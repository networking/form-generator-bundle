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
use Networking\InitCmsBundle\Model\ContentInterface;
use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;

/**
 * FormPageContent
 *
 * @ORM\Table(name="form_page_content")
 * @ORM\Entity
 */
class FormPageContent implements ContentInterface
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    private $id;

    /**
     * @var Form
     * @Sonata\FormMapper(
     *      name="form",
     *      type="networking_type_autocomplete",
     *      options={
     *          "label" = "form.label.form",
     *          "translation_domain" = "formGenerator",
     *          "class"="Networking\FormGeneratorBundle\Entity\Form",
     *          "attr"={"style"="width: 220px;"}
     *      }
     *  )
     *
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
     * @return $this
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getTemplateOptions($params = array())
    {
        return array(
            'form_page_content' => $this->form,
            'params' => $params,
        );
    }

    /**
     * @return array
     */
    public function getAdminContent()
    {
        return array(
            'content' => array('form_page_content' => $this->form),
            'template' => 'NetworkingFormGeneratorBundle:Admin:formPageContent.html.twig'
        );
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Custom Form';
    }
}
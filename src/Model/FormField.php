<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * FormField.
 *
 * @ORM\Table(name="form_field")
 * @ORM\Entity
 * @UniqueEntity(fields={"form", "name"}, message="Duplicate Id Field")
 */
class FormField extends BaseFormField
{
    use FormFieldTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @var Form
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Model\Form", inversedBy="formFields")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $form;


    public function __clone()
    {
        $this->id = null;
    }
}

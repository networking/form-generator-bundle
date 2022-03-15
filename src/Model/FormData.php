<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * FormData.
 *
 * @ORM\Table(name="form_data")
 * @ORM\Entity
 */
class FormData extends BaseFormData
{
    use FormDataTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Model\Form", inversedBy="formData" )
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $form;

    /**
     * @var ArrayCollection;
     *
     * @ORM\OneToMany(targetEntity="Networking\FormGeneratorBundle\Model\FormFieldData",cascade={"persist", "remove"}, mappedBy="formData", orphanRemoval=true)
     */
    protected $formFields = [];

}

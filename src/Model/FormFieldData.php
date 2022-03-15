<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * FieldData.
 *
 * @ORM\Table(name="form_field_data")
 * @ORM\Entity
 */
class FormFieldData extends BaseFormFieldData
{

    use FormFieldDataTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var FormData
     *
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Model\FormData", inversedBy="formFields")
     * @ORM\JoinColumn(name="form_data_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $formData;

}

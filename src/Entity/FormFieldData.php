<?php

namespace Networking\FormGeneratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FieldData
 *
 * @ORM\Table(name="form_field_data")
 * @ORM\Entity
 */
class FormFieldData
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var FormData
     *
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Entity\FormData", inversedBy="formFields")
     * @ORM\JoinColumn(name="form_data_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $formData;

    /**
     * @var string
     * @ORM\Column(name="label", type="text")
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="json")
     */
    private $value;


    /**
     * Get id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set formData.
     *
     * @param FormData $formData
     *
     * @return FormFieldData
     */
    public function setFormData(FormData $formData)
    {
        $this->formData = $formData;

        return $this;
    }

    /**
     * Get formData.
     *
     * @return FormData
     */
    public function getFormData()
    {
        return $this->formData;
    }


    /**
     * Set value.
     *
     * @param FormField $formField
     * @param $value
     * @return $this
     */
    public function setFormFieldValue(FormField $formField, $value)
    {
        if (is_array($value) && $map = $formField->getValueMap()) {
            foreach ($value as $key => $val) {
                $value[$key] = $val; //$map[$val];
            }
        }

        $this->value = $value;

        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

}


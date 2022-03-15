<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseFormFieldData
{

    /**
     *
     */
    protected $formData;

    /**
     * @var string
     * @ORM\Column(name="label", type="text")
     */
    protected $label;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="json")
     */
    protected $value;

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
     *
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
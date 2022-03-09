<?php

namespace Networking\FormGeneratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * FormData.
 *
 * @ORM\Table(name="form_data")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class FormData implements \ArrayAccess
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Entity\Form", inversedBy="formData" )
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $form;

    /**
     * @var ArrayCollection;
     *
     * @ORM\OneToMany(targetEntity="Networking\FormGeneratorBundle\Entity\FormFieldData",cascade={"persist", "remove"}, mappedBy="formData", orphanRemoval=true)
     */
    private $formFields = [];

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return FormData
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set form.
     *
     * @param Form $form
     *
     * @return FormData
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get fields.
     *
     * @return FormFieldData[]
     */
    public function getFormFields()
    {
        return $this->formFields;
    }

    /**
     * @param ArrayCollection $formFields
     */
    public function setFormFields($formFields)
    {
        $this->formFields = new ArrayCollection();

        foreach ($formFields as $field) {
            $this->addFormField($field);
        }
    }

    /**
     * @param FormFieldData $formField
     */
    public function addFormField(FormFieldData $formField, $key)
    {
        $formField->setFormData($this);
        $this->formFields[$key] = $formField;
    }

    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    public function __set($offset, $value){
     return $this->offsetSet($offset, $value);
    }


    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->formFields);
    }


    public function offsetGet($offset)
    {
        if(!array_key_exists($offset, $this->formFields)){
            return null;
        }

        /** @var FormFieldData $field */
        $field = $this->formFields[$offset];
        return $field->getValue();
    }


    public function offsetSet($offset, $value): void
    {
        if(!array_key_exists($offset, $this->formFields)){
            return;
        }
        /** @var FormFieldData $field */
        $field = $this->formFields[$offset];

        $field->setValue($value);
    }


    public function offsetUnset($offset): void{
        if(!array_key_exists($offset, $this->formFields)){
            return;
        }
        unset($this->formFields[$offset]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }


}

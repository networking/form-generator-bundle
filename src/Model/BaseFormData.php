<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class BaseFormData implements \ArrayAccess
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;

    /**
     * @var BaseForm
     *
     */
    protected $form;

    /**
     * @var ArrayCollection;
     */
    protected $formFields = [];

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
     * @return $this
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
     * @return $this
     */
    public function setForm(BaseForm $form)
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
     * @return BaseFormFieldData[]
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
     * @param BaseFormFieldData $formField
     */
    public function addFormField(BaseFormFieldData $formField, $key)
    {
        $formField->setFormData($this);
        $this->formFields[$key] = $formField;
    }

    public function __get($offset)
    {
        return $this->offsetGet($offset);
    }

    public function __set($offset, $value)
    {
        return $this->offsetSet($offset, $value);
    }


    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->formFields);
    }


    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->formFields)) {
            return null;
        }

        /** @var BaseFormFieldData $field */
        $field = $this->formFields[$offset];

        return $field->getValue();
    }


    public function offsetSet($offset, $value): void
    {
        if (!array_key_exists($offset, $this->formFields)) {
            return;
        }
        /** @var BaseFormFieldData $field */
        $field = $this->formFields[$offset];

        $field->setValue($value);
    }


    public function offsetUnset($offset): void
    {
        if (!array_key_exists($offset, $this->formFields)) {
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
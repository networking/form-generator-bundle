<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\IgnoreRevertInterface;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class BaseFormData implements \ArrayAccess, \Stringable, IgnoreRevertInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'createdAt', type: 'datetime')]
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

    #[ORM\PrePersist]
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
        return $this->getFormFieldDataObject($offset) !== null;
    }


    public function offsetGet($offset): mixed
    {
        $formFieldData = $this->getFormFieldDataObject($offset);

        if($formFieldData){
            return $formFieldData->getValue();
        }
        return null;
    }

    public function offsetSet($offset, $value): void
    {
        $field = $this->getFormFieldDataObject($offset);
        /** @var BaseFormFieldData $field */
        if($field instanceof BaseFormFieldData){
            $field->setValue($value);
        }

    }


    public function offsetUnset($offset): void
    {
        if($this->formFields instanceof Collection){
            $this->formFields->remove($offset);
            return ;
        }

        if (!array_key_exists($offset, $this->formFields)) {
            return;
        }
        unset($this->formFields[$offset]);
    }


    protected function getFormFieldDataObject($offset): ?BaseFormFieldData
    {
        $field = null;
        if($this->formFields instanceof Collection){
            $field = $this->formFields->get($offset);
        }

        if(is_array($this->formFields) && array_key_exists($offset, $this->formFields)) {
            $field = $this->formFields[$offset];
        }


        if($field instanceof BaseFormFieldData){
            return $field;
        }

        return null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }

}

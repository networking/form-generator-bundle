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
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'createdAt', type: 'datetime')]
    protected \DateTime $createdAt;

    protected BaseForm $form;

    /**
     * @var Collection<int, BaseFormFieldData>|array<int, BaseFormFieldData>;
     */
    protected Collection|array $formFields;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setForm(BaseForm $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function getForm(): BaseForm|Form
    {
        return $this->form;
    }

    /**
     * @return Collection<int, BaseFormFieldData>|array<int, BaseFormFieldData>
     */
    public function getFormFields(): array|Collection
    {
        return $this->formFields;
    }

    public function setFormFields(ArrayCollection $formFields): void
    {
        $this->formFields = new ArrayCollection();

        foreach ($formFields as $key => $field) {
            $this->addFormField($field, $key);
        }
    }

    public function addFormField(BaseFormFieldData $formField, $key): void
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
        $this->offsetSet($offset, $value);
    }

    public function __isset($offset): bool
    {
        return $this->offsetExists($offset);
    }

    public function offsetExists($offset): bool
    {
        return null !== $this->getFormFieldDataObject($offset);
    }

    public function offsetGet($offset): mixed
    {
        $formFieldData = $this->getFormFieldDataObject($offset);

        if ($formFieldData) {
            return $formFieldData->getValue();
        }

        return null;
    }

    public function offsetSet($offset, $value): void
    {
        $field = $this->getFormFieldDataObject($offset);
        /** @var BaseFormFieldData $field */
        if ($field instanceof BaseFormFieldData) {
            $field->setValue($value);
        }
    }

    public function offsetUnset($offset): void
    {
        if ($this->formFields instanceof Collection) {
            $this->formFields->remove($offset);

            return;
        }

        if (!array_key_exists($offset, $this->formFields)) {
            return;
        }
        unset($this->formFields[$offset]);
    }

    protected function getFormFieldDataObject($offset): ?BaseFormFieldData
    {
        $field = null;
        if ($this->formFields instanceof Collection) {
            $field = $this->formFields->get($offset);
        }

        if (is_array($this->formFields) && array_key_exists($offset, $this->formFields)) {
            $field = $this->formFields[$offset];
        }

        if ($field instanceof BaseFormFieldData) {
            return $field;
        }

        return null;
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}

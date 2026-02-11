<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class BaseFormFieldData
{
    protected BaseFormData $formData;

    #[ORM\Column(name: 'label', type: 'text')]
    protected string $label;

    #[ORM\Column(name: 'value', type: 'json', nullable: true)]
    protected array|string|null $value = null;

    public function setFormData(BaseFormData $formData): static
    {
        $this->formData = $formData;

        return $this;
    }

    public function getFormData(): BaseFormData
    {
        return $this->formData;
    }

    public function setFormFieldValue(FormField $formField, $value): static
    {
        if (is_array($value) && $map = $formField->getValueMap()) {
            foreach ($value as $key => $val) {
                $value[$key] = $val; // $map[$val];
            }
        }

        $this->value = $value;

        return $this;
    }

    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): array|string|null
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(?string $label = null): static
    {
        $this->label = $label;

        return $this;
    }
}

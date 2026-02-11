<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\MappedSuperclass]
abstract class BaseFormField
{
    public const array TEXT_FIELDS = [
        'Text Input',
        'Password Input',
        'Search Input',
        'Prepended Text',
        'Prepended Icon',
        'Appended Text',
        'Appended Icon',
        'Text Area',
    ];

    public const array SINGLE_CHOICE_FIELDS = [
        'Select Basic',
        'Multiple Radios',
        'Inline Radios',
        'Multiple Radios Inline',
    ];

    public const array MULTI_CHOICE_FIELDS = [
        'Select Multiple',
        'Multiple Checkboxes',
        'Inline Checkboxes',
        'Multiple Checkboxes Inline',
    ];

    public const array NON_VALUE_FIELDS = [
        'Legend',
        'Infotext',
    ];

    protected ?BaseForm $form = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    protected string $name;

    #[ORM\Column(name: 'field_label', type: 'string', length: 255)]
    protected string $fieldLabel;

    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    protected string $type;

    #[ORM\Column(name: 'options', type: 'json')]
    protected array $options = [];

    #[ORM\Column(name: 'placeholder', type: 'string', length: 255, nullable: true)]
    protected ?string $placeholder = null;

    #[ORM\Column(name: 'mandatory', type: 'boolean', nullable: true)]
    protected ?bool $mandatory = null;

    #[ORM\Column(name: 'invalid_message', type: 'string', length: 510, nullable: true)]
    protected ?string $invalidMessage = null;

    #[ORM\Column(name: 'empty_message', type: 'string', length: 510, nullable: true)]
    protected ?string $emptyMessage = null;

    #[ORM\Column(name: 'validation_type', type: 'string', length: 255, nullable: true)]
    protected ?string $validationType = null;

    #[Gedmo\SortablePosition]
    #[ORM\Column(name: 'position', type: 'integer')]
    protected ?int $position = null;

    #[Ignore]
    protected array $mappable
        = [
            'Select Basic' => ['options' => 'options', 'values' => 'values'],
            'Select Multiple' => ['options' => 'options', 'values' => 'values'],
            'Multiple Checkboxes' => [
                'options' => 'checkboxes',
                'values' => 'checkboxesValues',
            ],
            'Multiple Checkboxes Inline' => [
                'options' => 'checkboxes',
                'values' => 'checkboxesValues',
            ],
            'Multiple Radios' => [
                'options' => 'radios',
                'values' => 'radiosValues',
            ],
            'Multiple Radios Inline' => [
                'options' => 'radios',
                'values' => 'radiosValues',
            ],
        ];

    abstract public function setId($id): static;

    public function getForm(): ?BaseForm
    {
        return $this->form;
    }

    public function setForm(?BaseForm $form): static
    {
        $this->form = $form;
        return $this;
    }

    public function setName(?string $name = null): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    public function setFieldLabel(?string $fieldLabel = null): static
    {
        $this->fieldLabel = $fieldLabel;

        return $this;
    }

    public function getFieldLabel(): ?string
    {
        return $this->fieldLabel ?? null;
    }

    public function setType($type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type ?? null;
    }

    public function setOptions($options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        $options = [];
        foreach ($this->options as $key => $option) {
            if (is_array($option) && array_key_exists('value', $option)) {
                $options[$key] = $option['value'];
            }

            if (!in_array($this->type, self::TEXT_FIELDS)
                && 'options' == $key
            ) {
                $options[$key] = $option;
            }

            if (in_array(
                $this->type,
                ['Multiple Checkboxes', 'Multiple Checkboxes Inline']
            )
                && 'checkboxes' == $key && array_key_exists(
                    'value',
                    $option)
            ) {
                $options['options'] = $option['value'];
            }

            if (in_array(
                $this->type,
                ['Multiple Radios', 'Multiple Radios Inline']
            )
                && 'radios' == $key && array_key_exists(
                    'value',
                    $option)
            ) {
                $options['options'] = $option['value'];
            }

            if (!is_array($option)) {
                $options[$key] = $option;
            }

            if ('textarea' == $key) {
                $options['placeholder'] = $option;
            }
        }

        return $options;
    }

    public function setPlaceholder(?string $placeholder = null): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function setMandatory(bool $mandatory): static
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function getMandatory(): bool
    {
        return $this->mandatory;
    }

    public function setInvalidMessage(?string $invalidMessage = null): static
    {
        $this->invalidMessage = $invalidMessage;

        return $this;
    }

    public function getInvalidMessage(): ?string
    {
        return $this->invalidMessage;
    }

    public function setEmptyMessage(?string $emptyMessage = null): static
    {
        $this->emptyMessage = $emptyMessage;

        return $this;
    }

    public function getEmptyMessage(): ?string
    {
        return $this->emptyMessage;
    }

    public function setValidationType(?string $validationType = null): static
    {
        $this->validationType = $validationType;

        return $this;
    }

    public function getValidationType(): ?string
    {
        return $this->validationType;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position = null): static
    {
        $this->position = $position;

        return $this;
    }

    public function getValueMap(): false|array
    {
        if (array_key_exists($this->getType(), $this->mappable)) {
            $key = $this->mappable[$this->getType()];
            $options = $key['options'];

            if (array_key_exists($options, $this->options)
                && array_key_exists(
                    'value',
                    $this->options[$options]
                )
            ) {
                $choices = $this->options[$options]['value'];
                $valueMap = [];
                foreach ($choices as $choice => $value) {
                    $valueMap[$value] = $choice;
                }

                return $valueMap;
            }

            if (array_key_exists($options, $this->options)) {
                $choices = $this->options[$options];
                $valueMap = [];
                foreach ($choices as $choice => $value) {
                    $valueMap[$value] = $choice;
                }

                return $valueMap;
            }

            $choices = $this->options['options'];
            $valueMap = [];
            foreach ($choices as $choice => $value) {
                $valueMap[$value] = $choice;
            }

            return $valueMap;
        }

        return false;
    }
}

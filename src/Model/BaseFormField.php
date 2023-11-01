<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\MappedSuperclass]
abstract class BaseFormField
{

    public const TEXT_FIELDS  = [
            'Text Input',
            'Password Input',
            'Search Input',
            'Prepended Text',
            'Prepended Icon',
            'Appended Text',
            'Appended Icon',
            'Text Area',
        ];

    public const SINGLE_CHOICE_FIELDS  = [
            'Select Basic',
            'Multiple Radios',
            'Inline Radios',
        ];

    public const MULTI_CHOICE_FIELDS = [
            'Select Multiple',
            'Multiple Checkboxes',
            'Inline Checkboxes',
        ];

    public const NON_VALUE_FIELDS = [
            'Legend',
            'Infotext',
        ];

    /**
     * @var Form
     *
     */
    protected ?BaseForm $form = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    protected string $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'field_label', type: 'string', length: 255)]
    protected string $fieldLabel;

    /**
     * @var string
     */
    #[ORM\Column(name: 'type', type: 'string', length: 255)]
    protected string $type;

    /**
     * @var array
     */
    #[ORM\Column(name: 'options', type: 'json')]
    protected array $options = [];

    /**
     * @var string
     */
    #[ORM\Column(name: 'placeholder', type: 'string', length: 255, nullable: true)]
    protected $placeholder;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'mandatory', type: 'boolean', nullable: true)]
    protected $mandatory;

    /**
     * @var string
     */
    #[ORM\Column(name: 'invalid_message', type: 'string', length: 510, nullable: true)]
    protected $invalidMessage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'empty_message', type: 'string', length: 510, nullable: true)]
    protected $emptyMessage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'validation_type', type: 'string', length: 255, nullable: true)]
    protected $validationType;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     */
    #[ORM\Column(name: 'position', type: 'integer')]
    protected $position;

    /**
     * Mapping of choice fields to there option, value lists.
     *
     * @var array
     */
    #[Ignore]
    protected $mappable
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

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return FormField
     */
    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name??null;
    }

    /**
     * Set fieldLabel.
     *
     * @param string $fieldLabel
     *
     * @return FormField
     */
    public function setFieldLabel($fieldLabel)
    {
        $this->fieldLabel = $fieldLabel;

        return $this;
    }

    /**
     * Get fieldLabel.
     *
     * @return string
     */
    public function getFieldLabel(): ?string
    {
        return $this->fieldLabel??null;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return FormField
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type??null;
    }

    /**
     * Set options.
     *
     * @param string $options
     *
     * @return FormField
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->options as $key => $option) {
            if (is_array($option) && array_key_exists('value', $option)) {
                $options[$key] = $option['value'];
            }

            if (!in_array($this->type, self::TEXT_FIELDS)
                && $key == 'options'
            ) {
                $options[$key] = $option;
            }

            if (in_array(
                $this->type,
                ['Multiple Checkboxes', 'Multiple Checkboxes Inline']
            )
                && $key == 'checkboxes' && array_key_exists(
                    'value',
                    $option)
            ) {

                $options['options'] = $option['value'];
            }

            if (in_array(
                $this->type,
                ['Multiple Radios', 'Multiple Radios Inline']
            )
                && $key == 'radios' && array_key_exists(
                    'value',
                    $option)
            ) {
                $options['options'] = $option['value'];
            }

            if (!is_array($option)) {
                $options[$key] = $option;
            }

            if ($key == 'textarea') {
                $options['placeholder'] = $option;
            }
        }

        return $options;
    }

    /**
     * Set placeholder.
     *
     * @param string $placeholder
     *
     * @return FormField
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder.
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set mandatory.
     *
     * @param bool $mandatory
     *
     * @return FormField
     */
    public function setMandatory($mandatory)
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    /**
     * Get mandatory.
     *
     * @return bool
     */
    public function getMandatory()
    {
        return $this->mandatory;
    }

    /**
     * Set invalidMessage.
     *
     * @param string $invalidMessage
     *
     * @return FormField
     */
    public function setInvalidMessage($invalidMessage)
    {
        $this->invalidMessage = $invalidMessage;

        return $this;
    }

    /**
     * Get invalidMessage.
     *
     * @return string
     */
    public function getInvalidMessage()
    {
        return $this->invalidMessage;
    }

    /**
     * Set emptyMessage.
     *
     * @param string $emptyMessage
     *
     * @return FormField
     */
    public function setEmptyMessage($emptyMessage)
    {
        $this->emptyMessage = $emptyMessage;

        return $this;
    }

    /**
     * Get emptyMessage.
     *
     * @return string
     */
    public function getEmptyMessage()
    {
        return $this->emptyMessage;
    }

    /**
     * Set validationType.
     *
     * @param string $validationType
     *
     * @return FormField
     */
    public function setValidationType($validationType)
    {
        $this->validationType = $validationType;

        return $this;
    }

    /**
     * Get validationType.
     *
     * @return string
     */
    public function getValidationType()
    {
        return $this->validationType;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition(mixed $position)
    {
        $this->position = $position;
    }

    public function getValueMap()
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

            if(array_key_exists($options, $this->options)){
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

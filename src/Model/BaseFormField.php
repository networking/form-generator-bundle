<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

#[ORM\MappedSuperclass]
abstract class BaseFormField
{

    public const TEXT_FIELDS = ['Text Input', 'Password Input', 'Search Input', 'Prepended Text', 'Prepended Icon', 'Appended Text', 'Appended Icon', 'Text Area' ];

    /**
     * @var Form
     *
     */
    protected BaseForm $form;

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
    #[Serializer\Type('array')]
    #[ORM\Column(name: 'options', type: 'json')]
    protected array $options;

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
    #[Serializer\Exclude()]
    protected $mappable = [
        'Select Basic' => ['options' => 'options', 'values' => 'values'],
        'Select Multiple' => ['options' => 'options', 'values' => 'values'],
        'Multiple Checkboxes' => ['options' => 'checkboxes', 'values' => 'checkboxesValues'],
        'Multiple Checkboxes Inline' => ['options' => 'checkboxes', 'values' => 'checkboxesValues'],
        'Multiple Radios' => ['options' => 'radios', 'values' => 'radiosValues'],
        'Multiple Radios Inline' => ['options' => 'radios', 'values' => 'radiosValues'],
    ];

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
    public function setName($name)
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
        return $this->name;
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
    public function getFieldLabel()
    {
        return $this->fieldLabel;
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
    public function getType()
    {
        return $this->type;
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

        if(in_array($this->type, self::TEXT_FIELDS) && !array_key_exists('required', $this->options)){
            return $this->options + ['required' => ['label' => 'Required', 'name' => 'required', 'type' => 'checkbox', 'value' => false]];
        }
        return $this->options;
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
            $map = [];
            $key = $this->mappable[$this->getType()];
            $options = $key['options'];
            $values = $key['values'];

            foreach ($this->options[$values]['value'] as $k => $val) {
                if (array_key_exists($k, $this->options[$options]['value'])) {
                    $map[$this->options[$options]['value'][$k]] = $val;
                }
            }

            return $map;
        }

        return false;
    }
}

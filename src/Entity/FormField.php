<?php

namespace Networking\FormGeneratorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * FormField
 *
 * @ORM\Table(name="form_field")
 * @ORM\Entity
 */
class FormField
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
     * @var Form
     *
     * @Gedmo\SortableGroup
     *
     * @ORM\ManyToOne(targetEntity="Networking\FormGeneratorBundle\Entity\Form", inversedBy="formFields")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $form;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="field_label", type="string", length=255)
     */
    private $fieldLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var array
     *
     * @ORM\Column(name="options", type="json")
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(name="placeholder", type="string", length=255, nullable =true)
     */
    private $placeholder;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mandatory", type="boolean", nullable =true)
     */
    private $mandatory;

    /**
     * @var string
     *
     * @ORM\Column(name="invalid_message", type="string", length=510, nullable =true)
     */
    private $invalidMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="empty_message", type="string", length=510, nullable =true)
     */
    private $emptyMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="validation_type", type="string", length=255, nullable =true)
     */
    private $validationType;


    /**
     * @var string
     *
     * @ORM\Column(name="mapping", type="string", length=255, nullable =true)
     */
    private $mapping;



    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * Mapping of choice fields to there option, value lists
     *
     * @var array
     */
    protected $mappable = [
        'Select Basic' => ['options' => 'options', 'values' => 'values'],
        'Select Multiple' => ['options' => 'options', 'values' => 'values'],
        'Multiple Checkboxes' => ['options' => 'checkboxes', 'values' => 'checkboxesValues'],
        'Multiple Checkboxes Inline' => ['options' => 'checkboxes', 'values' => 'checkboxesValues'],
        'Multiple Radios' => ['options' => 'radios', 'values' => 'radiosValues'],
        'Multiple Radios Inline' => ['options' => 'radios', 'values' => 'radiosValues']
    ];

    public function __clone()
    {
        $this->id = null;
    }


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
    public function getName()
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
     * @param boolean $mandatory
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
     * @return boolean
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

    /**
     * @param mixed $position
     */
    public function setPosition($position)
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
                if(array_key_exists($k, $this->options[$options]['value'])){

                    $map[$this->options[$options]['value'][$k]] = $val;
                }
            }
            return $map;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param string $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }





}


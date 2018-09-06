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
class FormData
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
    private $formFields;


    /**
     * @var integer
     *
     * @ORM\Column(name="address_id", type="integer", nullable =true)
     */
    private $addressId;


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
     * @return array
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
     * @return int
     */
    public function getAddressId()
    {
        return $this->addressId;
    }

    /**
     * @param int $addressId
     */
    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;
    }




    /**
     * @param FormFieldData $formField
     */
    public function addFormField(FormFieldData $formField)
    {
        $formField->setFormData($this);
        $this->formFields[] = $formField;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }
}

<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Gedmo\Sluggable\Util\Urlizer;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\MappedSuperclass]
abstract class BaseForm implements \Stringable
{
    public const EMAIL = 'email';
    public const DB = 'db';
    public const EMAIL_DB = 'email_db';

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    protected $name;

    /**
     * @var string
     */
    #[ORM\Column(name: 'info_text', type: 'text', nullable: true)]
    protected $infoText;

    /**
     * @var string
     */
    #[ORM\Column(name: 'thank_you_text', type: 'text', nullable: true)]
    protected $thankYouText;

    /**
     * @var string
     */
    #[ORM\Column(name: 'email', type: 'text', nullable: true)]
    protected $email;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[ORM\Column(name: 'action', type: 'string', length: 255)]
    protected $action = 'email';

    /**
     * @var string
     */
    #[ORM\Column(name: 'redirect', type: 'string', length: 255, nullable: true)]
    protected $redirect;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'online', type: 'boolean', nullable: true)]
    protected $online = true;

    /**
     * @var ArrayCollection;
     */
    protected $formFields;

    /**
     * @var ArrayCollection;
     */
    protected $formData;

    /**
     * @var array
     */
    #[Ignore]
    protected $collection = [];

    public function __construct()
    {
        $this->formData = new ArrayCollection();
        $this->formFields = new ArrayCollection();
    }

    public function __clone()
    {
        $this->formData = new ArrayCollection();
        $this->formFields = new ArrayCollection();
        $date = new \DateTime();
        $this->name = $this->name.' copy '.$date->format('d.m.Y H:i:s');
    }

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        // check if the name is actually a fake name
        if ($this->isEmailAction() && !$this->getEmail()) {
            $context
                ->buildViolation('Email address needed for this type of action')
                ->atPath('email')
                ->addViolation();
        }

        if ($this->getEmail()) {
            $emailArr = explode(',', (string) $this->getEmail());
            foreach ($emailArr as $email) {
                if (!preg_match('/^.+\@\S+\.\S+$/', trim($email))) {
                    $context
                        ->buildViolation('%email% is not a valid email address', ['%email%' => $email])
                        ->atPath('email')
                        ->addViolation();
                    break;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isEmailAction()
    {
        return in_array($this->getAction(), [self::EMAIL, self::EMAIL_DB]);
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    public function setAction(mixed $action)
    {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail(mixed $email)
    {
        $this->email = $email;
    }

    /**
     * Get infoText.
     *
     * @return string
     */
    public function getInfoText()
    {
        return $this->infoText;
    }

    /**
     * Set infoText.
     *
     * @param string $infoText
     *
     * @return Form
     */
    public function setInfoText($infoText)
    {
        $this->infoText = $infoText;

        return $this;
    }

    /**
     * Get thankyouText.
     *
     * @return string
     */
    public function getThankYouText()
    {
        return $this->thankYouText;
    }

    /**
     * Set thankYouText.
     *
     * @param string $thankYouText
     *
     * @return Form
     */
    public function setThankYouText($thankYouText)
    {
        $this->thankYouText = $thankYouText;

        return $this;
    }

    public function removeFields()
    {
        foreach ($this->getFormFields() as $field) {
            $this->formFields->removeElement($field);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @param ArrayCollection $formData
     */
    public function setFormData($formData)
    {
        $this->formData = $formData;
    }

    /**
     * @return mixed
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    public function setRedirect(mixed $redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return array
     */
    #[Ignore]
    public function getFormFieldConfiguration()
    {
        foreach ($this->getFormFields() as $formField) {
            $this->collection[] = [
                'id' => $formField->getName(),
                'type' => $formField->getType(),
                'value' => $formField->getFieldLabel(),
                'options' => $formField->getOptions(),
            ];
        }

        return $this->collection;
    }

    /**
     * @return ArrayCollection
     */
    public function getFormFields()
    {
        return $this->formFields??[];
    }

    /**
     * @param ArrayCollection $formFields
     */
    public function setFormFields($formFields)
    {
        foreach ($formFields as $field) {
            $this->addFormField($field);
        }
    }

    /**
     * @param FormField $formField
     */
    public function addFormField(BaseFormField $formField)
    {

        if($this->formFields->filter(fn($field) => $field->getId() == $formField->getId())->count()){
            return;
        }
        $formField->setForm($this);
        $this->formFields->add($formField);
    }

    public function __toString(): string
    {
        return $this->getName();
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
     * Set name.
     *
     * @param string $name
     *
     * @return Form
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online ?? true;
    }

    /**
     * @param bool $online
     */
    public function setOnline(?bool $online): void
    {
        $this->online = $online;
    }

    /**
     * @return bool
     */
    public function isDbAction()
    {
        return in_array($this->getAction(), [self::DB, self::EMAIL_DB]);
    }

    /**
     * @param $key
     *
     * @return FormField
     */
    public function getField($key)
    {
        $fields = $this->formFields->filter(fn($field) => Urlizer::urlize($field->getName()) == $key);

        if ($fields->count() > 0) {
            return $fields->first();
        }

        return false;
    }
}

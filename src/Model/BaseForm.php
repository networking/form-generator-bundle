<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Util\Urlizer;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\MappedSuperclass]
abstract class BaseForm implements \Stringable
{
    public const string EMAIL = 'email';
    public const string DB = 'db';
    public const string EMAIL_DB = 'email_db';

    #[Assert\NotBlank]
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    protected string $name;

    #[ORM\Column(name: 'info_text', type: 'text', nullable: true)]
    protected ?string $infoText = null;

    #[ORM\Column(name: 'thank_you_text', type: 'text', nullable: true)]
    protected ?string $thankYouText = null;

    #[ORM\Column(name: 'email', type: 'text', nullable: true)]
    protected ?string $email = null;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'action', type: 'string', length: 255)]
    protected string $action = self::EMAIL;

    #[ORM\Column(name: 'redirect', type: 'string', length: 255, nullable: true)]
    protected ?string $redirect = null;

    #[ORM\Column(name: 'online', type: 'boolean', nullable: true)]
    protected bool $online = true;

    protected Collection|array $formFields;

    protected Collection|array $formData;

    #[Ignore]
    protected array $collection = [];

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

    abstract public function getId(): ?int;
    abstract public function setId(?int $id = null): static;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
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

    public function isEmailAction(): bool
    {
        return in_array($this->getAction(), [self::EMAIL, self::EMAIL_DB]);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email = null): static
    {
        $this->email = $email;

        return $this;
    }

    public function getInfoText(): ?string
    {
        return $this->infoText;
    }

    public function setInfoText(?string $infoText): static
    {
        $this->infoText = $infoText;

        return $this;
    }

    public function getThankYouText(): ?string
    {
        return $this->thankYouText;
    }

    public function setThankYouText(?string $thankYouText): static
    {
        $this->thankYouText = $thankYouText;

        return $this;
    }

    public function removeFields(): void
    {
        foreach ($this->getFormFields() as $field) {
            $this->formFields->removeElement($field);
        }
    }

    /**
     * @return Collection<int, BaseFormData>
     */
    public function getFormData(): Collection
    {
        return $this->formData;
    }

    public function setFormData(?Collection $formData = null): void
    {
        $this->formData = $formData;
    }

    public function getRedirect(): ?string
    {
        return $this->redirect;
    }

    public function setRedirect(?string $redirect = null): void
    {
        $this->redirect = $redirect;
    }

    /**
     * @return Collection<int, BaseFormField>|array<int, BaseFormField>
     */
    public function getFormFields(): Collection|array
    {
        return $this->formFields ?? [];
    }

    public function setFormFields(Collection|array $formFields): static
    {
        foreach ($formFields as $field) {
            $this->addFormField($field);
        }

        return $this;
    }

    public function addFormField(BaseFormField $formField): static
    {
        $formField->setForm($this);
        $this->formFields->add($formField);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name = null): static
    {
        $this->name = $name;

        return $this;
    }

    public function isOnline(): bool
    {
        return $this->online ?? true;
    }

    public function setOnline(?bool $online): static
    {
        $this->online = $online;

        return $this;
    }

    public function isDbAction(): bool
    {
        return in_array($this->getAction(), [self::DB, self::EMAIL_DB]);
    }

    public function getField($key): false|FormField
    {
        $fields = $this->formFields->filter(fn ($field) => Urlizer::urlize($field->getName()) == $key);

        if ($fields->count() > 0) {
            return $fields->first();
        }

        return false;
    }

    #[Ignore]
    public function getFormFieldConfiguration(): array
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

    public function __toString(): string
    {
        return $this->getName();
    }
}

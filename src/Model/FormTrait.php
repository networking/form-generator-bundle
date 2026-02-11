<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

trait FormTrait
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * @var Collection<int, FormField>|array<int, FormField>;
     */
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormField::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection|array $formFields;

    /**
     * @var Collection<int, FormData>|array<int, FormData>;
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormData::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    protected Collection|array $formData;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id = null): static
    {
        $this->id = $id;
        return $this;
    }
}

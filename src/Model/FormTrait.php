<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

trait FormTrait
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var FormField[];
     */
    #[Serializer\Type("ArrayCollection<Networking\FormGeneratorBundle\Model\FormField>")]
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormField::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $formFields;

    /**
     * @var FormData[];
     */
    #[Serializer\Exclude(if: "true")]
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormData::class, cascade: ['remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    protected $formData;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}

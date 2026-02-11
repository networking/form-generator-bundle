<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait FormDataTrait
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'formData')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected BaseForm $form;

    /**
     * @var Collection<int, FormFieldData>|array<int, FormFieldData>;
     */
    #[ORM\OneToMany(mappedBy: 'formData', targetEntity: FormFieldData::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection|array $formFields;

    public function getId(): ?int
    {
        return $this->id;
    }
}

<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait FormFieldDataTrait
{
    #[ORM\ManyToOne(targetEntity: FormData::class, inversedBy: 'formFields')]
    #[ORM\JoinColumn(name: 'form_data_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected BaseFormData $formData;

    public function getId(): ?int
    {
        return $this->id;
    }
}

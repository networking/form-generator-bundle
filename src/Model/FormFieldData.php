<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'form_field_data')]
#[ORM\Entity]
class FormFieldData extends BaseFormFieldData
{
    use FormFieldDataTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FormData::class, inversedBy: 'formFields')]
    #[ORM\JoinColumn(name: 'form_data_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected BaseFormData $formData;
}

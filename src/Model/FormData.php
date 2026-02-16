<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'form_data')]
#[ORM\Entity]
class FormData extends BaseFormData
{
    use FormDataTrait;


    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'formData')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected BaseForm $form;

    /**
     * @var Collection<int, FormFieldData>|array<int, FormFieldData>;
     */
    #[ORM\OneToMany(targetEntity: FormFieldData::class, mappedBy: 'formData', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection|array $formFields;
}

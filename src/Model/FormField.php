<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Table(name: 'form_field')]
#[ORM\Entity]
#[UniqueEntity(fields: ['form', 'name'], message: 'Duplicate Id Field')]
class FormField extends BaseFormField
{
    use FormFieldTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'formFields')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?BaseForm $form = null;

    public function __clone()
    {
        $this->id = null;
    }

    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }
}

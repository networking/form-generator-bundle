<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait FormFieldTrait
{
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Form::class, inversedBy: 'formFields')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?BaseForm $form = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Table(name: 'form')]
#[ORM\Entity]
class Form extends BaseForm
{
    use FormTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormField::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected Collection|array $formFields;

    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormData::class, cascade: ['remove'], orphanRemoval: true)]
    #[Ignore]
    protected Collection|array $formData;

    public function __clone()
    {
        $this->id = null;
        parent::__clone();
    }


}

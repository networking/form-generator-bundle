<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Form.
 */
#[ORM\Table(name: 'form')]
#[ORM\Entity]
class Form extends BaseForm
{
    use FormTrait;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var FormField[];
     * @Serializer\Type("ArrayCollection<Networking\FormGeneratorBundle\Model\FormField>")
     */
    #[Serializer\Type("ArrayCollection<Networking\FormGeneratorBundle\Model\FormField>")]
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: FormField::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected $formFields;

    /**
     * @var FormData[];
     * @Serializer\Exclude(if="true")
     */
    #[Serializer\Exclude(if: 'true')]
    #[ORM\OneToMany(targetEntity: FormData::class, cascade: ['remove'], mappedBy: 'form', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    protected $formData;


    public function __clone()
    {
        $this->id = null;
        parent::__clone();
    }
}

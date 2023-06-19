<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

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
    #[ORM\OneToMany(targetEntity: \Networking\FormGeneratorBundle\Model\FormField::class, cascade: ['persist', 'remove'], mappedBy: 'form', orphanRemoval: true)]
    protected $formFields;

    /**
     * @var FormData[];
     * @Serializer\Exclude(if="true")
     */
    #[ORM\OneToMany(targetEntity: \Networking\FormGeneratorBundle\Model\FormData::class, cascade: ['remove'], mappedBy: 'form', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    protected $formData;


    public function __clone()
    {
        $this->id = null;
        parent::__clone();
    }
}

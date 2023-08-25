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
     */
    #[ORM\OneToMany(targetEntity: \Networking\FormGeneratorBundle\Model\FormField::class, cascade: ['persist', 'remove'], mappedBy: 'form', orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Serializer\Type("ArrayCollection<Networking\FormGeneratorBundle\Model\FormField>")]
    protected $formFields;

    /**
     * @var FormData[];
     */
    #[ORM\OneToMany(targetEntity: \Networking\FormGeneratorBundle\Model\FormData::class, cascade: ['remove'], mappedBy: 'form', orphanRemoval: true)]
    #[Serializer\Exclude(if: 'true')]
    protected $formData;


    public function __clone()
    {
        $this->id = null;
        parent::__clone();
    }
}

<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait FormDataTrait
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var Form
     */
    #[ORM\ManyToOne(targetEntity: \Networking\FormGeneratorBundle\Model\Form::class, inversedBy: 'formData')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $form;

    /**
     * @var ArrayCollection;
     */
    #[ORM\OneToMany(targetEntity: \Networking\FormGeneratorBundle\Model\FormFieldData::class, cascade: ['persist', 'remove'], mappedBy: 'formData', orphanRemoval: true)]
    protected $formFields = [];

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}

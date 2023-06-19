<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

trait FormFieldTrait
{

    /**
     * @var Form
     * @Gedmo\SortableGroup
     */
    #[ORM\ManyToOne(targetEntity: \Networking\FormGeneratorBundle\Model\Form::class, inversedBy: 'formFields')]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected BaseForm $form;

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

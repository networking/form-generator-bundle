<?php

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Form.
 *
 * @ORM\Table(name="form")
 * @ORM\Entity
 */
class Form extends BaseForm
{
    use FormTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}

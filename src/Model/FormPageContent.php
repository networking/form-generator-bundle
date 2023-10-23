<?php

declare(strict_types=1);

/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Model\ContentInterface;
use Networking\InitCmsBundle\Annotation as Sonata;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FormPageContent.
 */
#[ORM\Table(name: 'form_page_content')]
#[ORM\Entity]
class FormPageContent extends LayoutBlock implements ContentInterface
{
    /**
     * @var int
     *
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\ManyToOne(targetEntity: \Networking\FormGeneratorBundle\Model\Form::class)]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?\Networking\FormGeneratorBundle\Model\Form $form = null;

    /**
     * @Sonata\FormCallback
     */
    #[Sonata\FormCallback]
    public static function configureFormFields(FormMapper $formBuilder)
    {
        $formBuilder->add(
            'form',
            AutocompleteType::class,
            [
                'label' => 'form.label.form',
                'translation_domain' => 'formGenerator',
                'class' => Form::class,
                'attr' => ['style' => 'width: 220px;'],
                'layout' => 'horizontal',
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('f');
                    $qb->where('f.online = 1 OR f.online IS NULL');

                    return $qb;
                },
            ]
        );
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return $this
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getTemplateOptions($params = [])
    {
        return [
            'form_page_content' => $this->form,
            'params' => $params,
        ];
    }

    /**
     * @return array
     */
    public function getAdminContent():array
    {
        return [
            'content' => ['form_page_content' => $this->form],
            'template' => '@NetworkingFormGenerator/Admin/formPageContent.html.twig',
        ];
    }

    public function getContentTypeName(): string
    {
        return 'Custom Form';
    }
}

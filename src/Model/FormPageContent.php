<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Model;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Annotation as Sonata;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Form\Type\AutocompleteType;
use Networking\InitCmsBundle\Model\ContentInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'form_page_content')]
#[ORM\Entity]
class FormPageContent extends LayoutBlock implements ContentInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Form::class)]
    #[ORM\JoinColumn(name: 'form_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Form $form = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function getTemplateOptions($params = []): array
    {
        return [];
    }

    public function getAdminContent(): array
    {
        return [];
    }

    public function getContentTypeName(): string
    {
        return 'Custom Form';
    }
}

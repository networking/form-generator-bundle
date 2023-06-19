<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InfotextType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['label'] = false;
        $view->vars['text'] = $options['label'];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver): void
    {
        $resolver->setRequired([
            'text',
        ]);
    }
}

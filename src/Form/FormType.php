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

namespace Networking\FormGeneratorBundle\Form;

use Gedmo\Sluggable\Util\Urlizer;
use Networking\FormGeneratorBundle\Form\Type\InfotextType;
use Networking\FormGeneratorBundle\Form\Type\LegendType;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormField;
use Networking\FormGeneratorBundle\Model\FormData;
use Networking\FormGeneratorBundle\Model\FormFieldData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormType extends AbstractType
{
    final public const FRONTEND_INPUT_SIZES = [
        'xs' => 'col-2',
        's' => 'col-4',
        'm' => 'col-6',
        'l' => 'col-8',
        'xl' => 'col-10',
        'xxl' => 'col-12',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $form = $options['form'];

        if (!is_null($form)) {
            foreach ($form->getFormFields() as $key => $field) {
                if (!$name = $field->getName()) {
                    $name = $field->getType().$key;
                }
                $id = Urlizer::urlize($name);

                $builder->add(
                    $id,
                    $this->getFieldType($field->getType()),
                    $this->extractFieldOptions($field, $options)
                );
            }

            $builder->addModelTransformer(new FormDataTransformer($form, $options['data_class'], $options['form_field_data_class']));
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $setIdAttr = function (Options $options) {
            if ($options['form'] and $options['form'] instanceof BaseForm) {
                return ['id' => 'formgenerator_form_'.$options['form']->getId()];
            }

            return [];
        };
        $resolver->setRequired([
            'form',
        ]);
        $resolver->setDefaults([
            'is_admin' => false,
            'attr' => $setIdAttr,
            'horizontal' => true,
            'show_legend' => false,
            'render_fieldset' => false,
            'error_bubbling' => true,
            'csrf_protection' => false,
            'data_class' => FormData::class,
            'form_field_data_class' => FormFieldData::class,
            'frontend_css_input_sizes' => self::FRONTEND_INPUT_SIZES,
        ]);
        $resolver->setDefined([
            'translation_domain',
            'form',
        ]);
    }

    protected function getFieldType($type)
    {
        $type = match ($type) {
            'Legend' => LegendType::class,
            'Infotext' => InfotextType::class,
            'ckeditor' => InfotextType::class,
            'Password Input' => PasswordType::class,
            'Search Input' => SearchType::class,
            'Text Area' => TextareaType::class,
            'Multiple Checkboxes', 'Multiple Checkboxes Inline', 'Multiple Radios', 'Multiple Radios Inline', 'Select Basic', 'Select Multiple' => ChoiceType::class,
            default => TextType::class,
        };

        return $type;
    }

    protected function extractFieldOptions(BaseFormField $field, array $formOptions): array
    {
        $options = $field->getOptions();

        $fieldOptions = [];
        $fieldOptions['label'] = $field->getFieldLabel() ?: false;
        if (!$field->getFieldLabel()) {
            $fieldOptions['label_render'] = false;
        } else {
            $fieldOptions['label_render'] = true;
        }

        switch ($field->getType()) {
            case 'Legend':
            case 'Infotext':
                break;
            case 'Password Input':
                $fieldOptions['attr']['placeholder'] = $options['placeholder'];
                break;
            case 'Search Input':
                $fieldOptions['attr']['placeholder'] = $options['placeholder'];
                break;
            case 'Text Input':
                $fieldOptions['attr']['placeholder'] = $options['placeholder'];
                break;
            case 'Prepended Text':
            case 'Prepended Icon':
                $type = ('Prepended Text' == $field->getType()) ? 'text' : 'icon';
                $fieldOptions['attr']['placeholder'] = $options['placeholder'];
                $fieldOptions['widget_addon_prepend'] = [$type => $options['prepend']];
                break;
            case 'Appended Text':
            case 'Appended Icon':
                $type = ('Appended Text' == $field->getType()) ? 'text' : 'icon';
                $fieldOptions['attr']['placeholder'] = $options['placeholder'];
                $fieldOptions['widget_addon_append'] = [$type => $options['append']];
                break;
            case 'Text Area':
                $fieldOptions['attr']['placeholder'] = $options['placeholder'];
                break;
            case 'Multiple Checkboxes':
            case 'Multiple Checkboxes Inline':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = true;
                $fieldOptions['multiple'] = true;
                if ('Multiple Checkboxes Inline' == $field->getType()) {
                    $fieldOptions['label_attr'] = ['class' => 'checkbox-inline'];
                }
                break;
            case 'Multiple Radios':
            case 'Multiple Radios Inline':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = true;
                $fieldOptions['multiple'] = false;
                if ('Multiple Radios Inline' == $field->getType()) {
                    $fieldOptions['label_attr'] = ['class' => 'radio-inline'];
                }
                reset($fieldOptions['choices']);
                $fieldOptions['data'] = current($fieldOptions['choices']);
                break;
            case 'Select Basic':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = false;
                $fieldOptions['multiple'] = false;
                break;
            case 'Select Multiple':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = false;
                $fieldOptions['multiple'] = true;

                break;
            default:
                $type = 'text';
                break;
        }

        if (array_key_exists('required', $options)) {
            $fieldOptions['required'] = $options['required'];

            if ($fieldOptions['required']) {
                $fieldOptions['constraints'] = new NotBlank();
            }
        } elseif (!array_key_exists('required', $fieldOptions)) {
            $fieldOptions['required'] = false;
        }
        if (array_key_exists('helptext', $options)) {
            $fieldOptions['help'] = $options['helptext'];
        }

        if ($formOptions['is_admin']) {
            $fieldOptions['attr']['disabled'] = true;
            $fieldOptions['attr']['readonly'] = true;
        }

        return $fieldOptions;
    }

    public function getBlockPrefix(): string
    {
        return 'generated_form';
    }
}

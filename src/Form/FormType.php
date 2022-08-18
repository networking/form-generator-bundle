<?php
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
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormField;
use Networking\FormGeneratorBundle\Model\Form;
use Networking\FormGeneratorBundle\Model\FormData;
use Networking\FormGeneratorBundle\Model\FormField;
use Networking\FormGeneratorBundle\Model\FormFieldData;
use Networking\FormGeneratorBundle\Form\Type\InfotextType;
use Networking\FormGeneratorBundle\Form\Type\LegendType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
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

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $form = $options['form'];

        if (!is_null($form )) {
            foreach ($form->getFormFields() as $key =>  $field) {
                if(!$name = $field->getName()){
                    $name = $field->getType().$key;
                }
                $id = Urlizer::urlize($name);

                $builder->add(
                    $id,
                    $this->getFieldType($field->getType()),
                    $this->extractFieldOptions($field, $options)
                );
            }

            $builder->addModelTransformer(new FormDataTransformer($form, $options['data_class'], $options['form_field_data_class']) );
        }



    }


    public function configureOptions(OptionsResolver $resolver)
    {

        $setIdAttr = function(Options $options){
              if($options['form'] and $options['form'] instanceof BaseForm){
                  return ['id' => 'formgenerator_form_'.$options['form']->getId()];
              }
              return [];
        };
        $resolver->setRequired([
            'form',
        ]);
        $resolver->setDefaults([
            'attr' => $setIdAttr,
            'horizontal' => true,
            'show_legend' => false,
            'render_fieldset' => false,
            'error_bubbling' => true,
            'csrf_protection' => false,
            'error_type' => 'block',
            'data_class' => FormData::class,
            'form_field_data_class' => FormFieldData::class


        ]);
        $resolver->setDefined([
            'translation_domain',
            'form',
        ]);
    }

    protected function getFieldType($type)
    {
        switch ($type) {
            case 'Legend':
                $type = LegendType::class;
                break;
            case 'Infotext':
                $type = InfotextType::class;
                break;
            case 'Password Input':
                $type = PasswordType::class;
                break;
            case 'Search Input':
                $type = SearchType::class;
                break;
            case 'Text Area':
                $type = TextareaType::class;
                break;
            case 'Multiple Checkboxes':
            case 'Multiple Checkboxes Inline':
            case 'Multiple Radios':
            case 'Multiple Radios Inline':
            case 'Select Basic':
            case 'Select Multiple':
                $type = ChoiceType::class;
                break;
            default:
                $type = TextType::class;
                break;
        }

        return $type;
    }

    protected function extractFieldOptions(BaseFormField $field, array $formOptions)
    {
        $options = $field->getOptions();

        $fieldOptions = [];
        $fieldOptions['label'] = $field->getFieldLabel() ? $field->getFieldLabel() : false;
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
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                break;
            case 'Search Input':
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                break;
            case 'Text Input':
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                break;
            case 'Prepended Text':
            case 'Prepended Icon':
                $type = ($field->getType() == 'Prepended Text') ? 'text' : 'icon';
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                $fieldOptions['widget_addon_prepend'] = [$type => $options['prepend']['value']];
                break;
            case 'Appended Text':
            case 'Appended Icon':
                $type = ($field->getType() == 'Appended Text') ? 'text' : 'icon';
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                $fieldOptions['widget_addon_append'] = [$type => $options['append']['value']];
                break;
            case 'Text Area':
                $fieldOptions['attr']['placeholder'] = $options['textarea']['value'];
                break;
            case 'Multiple Checkboxes':
            case 'Multiple Checkboxes Inline':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = true;
                $fieldOptions['multiple'] = true;
                $fieldOptions['required'] = true;
                if ($field->getType() == 'Multiple Checkboxes Inline') {
                    $fieldOptions['widget_type'] = 'inline';
                }
                $fieldOptions['constraints'] = new NotBlank();
                break;
            case 'Multiple Radios':
            case 'Multiple Radios Inline':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = true;
                $fieldOptions['multiple'] = false;
                $fieldOptions['required'] = true;
                if ($field->getType() == 'Multiple Radios Inline') {
                    $fieldOptions['widget_type'] = 'inline';
                }
                reset($fieldOptions['choices']);
                $fieldOptions['data'] = key($fieldOptions['choices']);
                $fieldOptions['constraints'] = new NotBlank();
                break;
            case 'Select Basic':
                $fieldOptions['choices'] = $field->getValueMap();
                $fieldOptions['expanded'] = false;
                $fieldOptions['multiple'] = false;
                $fieldOptions['required'] = true;
                $fieldOptions['constraints'] = new NotBlank();
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
            $fieldOptions['required'] = $options['required']['value'];
            if ($fieldOptions['required']) {
                $fieldOptions['constraints'] = new NotBlank();
            }
        } elseif (!array_key_exists('required', $fieldOptions)) {
            $fieldOptions['required'] = false;
        }
        if (array_key_exists('helptext', $options)) {
            $fieldOptions['help_block'] = $options['helptext']['value'];
        }

        if ($formOptions['horizontal']) {
            $fieldOptions['layout'] = 'horizontal';
        }

        if ($formOptions['label_attr']) {
            $fieldOptions['label_attr'] = $formOptions['label_attr'];
        }

        if (!$fieldOptions['label_render']) {
            $fieldOptions['horizontal_label_offset_class'] = ' ';
            $fieldOptions['horizontal_input_wrapper_class'] = 'col-md-12';
        }

        $fieldOptions['error_type'] = $formOptions['error_type'];

        return $fieldOptions;
    }


    public function getBlockPrefix()
    {
        return 'generated_form';
    }
}

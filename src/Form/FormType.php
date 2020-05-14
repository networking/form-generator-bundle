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

use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Entity\FormField;
use Networking\FormGeneratorBundle\Form\Type\LegendType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Form $form */
        $form = $options['form'];

        if (!is_null($form)) {
            foreach ($form->getFormFields() as $field) {
                $builder->add(
                    self::slugify($field->getName()),
                    $this->getFieldType($field->getType()),
                    $this->extractFieldOptions($field, $options)
                );
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'form',
        ]);
        $resolver->setDefaults([
            'horizontal' => true,
            'show_legend' => false,
            'render_fieldset' => false,
            'error_bubbling' => true,
            'csrf_protection' => false,
            'error_type' => 'block',

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

    protected function extractFieldOptions(FormField $field, array $formOptions)
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
                break;
            case 'Password Input':
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                break;
            case 'Search Input':
                //search input wird als Text Info Feld "missbraucht"
//                echo "123";
//                print_r($options);die;
                $fieldOptions['attr']['value'] = $options['textarea']['value'];
                $fieldOptions['attr']['formType'] = 'Search Input';
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
                if ($field->getType() == 'Multiple Checkboxes Inline') {
                    $fieldOptions['widget_type'] = 'inline';
                }
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
            }else{

                //bei radio buttons die "none" option ausblenden
                if($field->getType() == 'Multiple Radios' or $field->getType() == 'Multiple Radios Inline')
                {
                    $fieldOptions['required'] = false;
                    $fieldOptions['data'] = '';
                    $fieldOptions['placeholder'] = false;
                    unset($fieldOptions['constraints'] ); ///= false;

                }

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

        if (!$fieldOptions['label_render']) {
            $fieldOptions['horizontal_label_offset_class'] = ' ';
            $fieldOptions['horizontal_input_wrapper_class'] = 'col-md-12';
        }

        $fieldOptions['error_type'] = $formOptions['error_type'];

        return $fieldOptions;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function getBlockPrefix()
    {
        return 'generated_form';
    }
}

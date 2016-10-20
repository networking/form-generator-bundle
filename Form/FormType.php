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
use Networking\FormGeneratorBundle\Entity\FormField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Networking\FormGeneratorBundle\Entity\Form;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class FormType extends AbstractType
{
    protected $form = null;

    public function __construct(array $options)
    {
        if (array_key_exists('form', $options)) {
            $this->form = $options['form'];
        }
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        /** @var Form $form */
        $form = $options['form'];

        if(!is_null($form)){
            foreach ($form->getFormFields() as $field) {

                $builder->add(
                    self::slugify($field->getName()),
                    $this->getFieldType($field->getType()),
                    $this->extractFieldOptions($field, $options)
                );
            }
        }


    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'form'
        ));
        $resolver->setDefaults(array(
            'horizontal' => true,
            'show_legend' => false,
            'render_fieldset' => false,
            'form' => $this->form,
            'error_bubbling' => true,
            'csrf_protection' => false
        ));
        $resolver->setOptional(array(
            'translation_domain'
        ));
    }

    protected function getFieldType($type)
    {
        switch ($type) {
            case 'Legend':
                $type = 'form_legend';
                break;
            case 'Password Input':
                $type = 'password';
                break;
            case 'Search Input':
                $type = 'search';
                break;
            case 'Text Area':
                $type = 'textarea';
                break;
            case 'Multiple Checkboxes':
            case 'Multiple Checkboxes Inline':
            case 'Multiple Radios':
            case 'Multiple Radios Inline':
            case 'Select Basic':
            case 'Select Multiple':
                $type = 'choice';
                break;
            default:
                $type = 'text';
                break;
        }

        return $type;
    }

    protected function extractFieldOptions(FormField $field, array $formOptions)
    {
        $options = $field->getOptions();

        $fieldOptions = array();
        $fieldOptions['label'] = $field->getFieldLabel() ? $field->getFieldLabel() : false;
        if (!$field->getFieldLabel()) {
            $fieldOptions['label_render'] = false;
        }else{
            $fieldOptions['label_render'] = true;
        }


        switch ($field->getType()) {
            case 'Legend':
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
                $fieldOptions['widget_addon_prepend'] = array($type => $options['prepend']['value']);
                break;
            case 'Appended Text':
            case 'Appended Icon':
                $type = ($field->getType() == 'Appended Text') ? 'text' : 'icon';
                $fieldOptions['attr']['placeholder'] = $options['placeholder']['value'];
                $fieldOptions['widget_addon_append'] = array($type => $options['append']['value']);
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
            if($fieldOptions['required'] ){
                $fieldOptions['constraints'] = new NotBlank();
            }

        } elseif (!array_key_exists('required', $fieldOptions)) {
            $fieldOptions['required'] = false;
        }
        if (array_key_exists('helptext', $options)) {
            $fieldOptions['help_block'] = $options['helptext']['value'];
        }
        if ($formOptions['horizontal']) {
            $fieldOptions['horizontal'] = true;
            $fieldOptions['horizontal_label_class'] = 'col-md-4';
            $fieldOptions['horizontal_input_wrapper_class'] = 'col-md-8';
        }

        if(!$fieldOptions['label_render']){
            $fieldOptions['horizontal_label_offset_class'] = ' ';
            $fieldOptions['horizontal_input_wrapper_class'] = 'col-md-12';
        }


        return $fieldOptions;
    }

    /**
     * @param $text
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

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'custom_form';
    }
}
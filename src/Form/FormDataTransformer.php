<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Form;

use Gedmo\Sluggable\Util\Urlizer;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormData;
use Networking\FormGeneratorBundle\Model\BaseFormField;
use Networking\FormGeneratorBundle\Model\Form;
use Networking\FormGeneratorBundle\Model\FormData;
use Networking\FormGeneratorBundle\Model\FormFieldData;
use Symfony\Component\Form\DataTransformerInterface;

class FormDataTransformer implements DataTransformerInterface
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct(BaseForm $form, protected $dataClass, protected $formFieldDataClass){
        $this->form = $form;
    }

    /**
     * @param $value
     */
    public function transform($value): BaseFormData
    {

        $value = new $this->dataClass;

        $value->setForm($this->form);
        foreach ($this->form->getFormFields() as $key =>  $field) {
            if(in_array($field->getType(), BaseFormField::NON_VALUE_FIELDS)){
                continue;
            }

            if(!$name = $field->getName()){
                $name = $field->getType().$key;
            }
            $id = Urlizer::urlize($name);

            $formFieldData = new $this->formFieldDataClass;
            $formFieldData->setLabel($field->getFieldLabel());


            $value->addFormField($formFieldData, $id);

        }

        return $value;
    }

    /**
     * @param $value
     */
    public function reverseTransform($value): mixed
    {
        foreach ($this->form->getFormFields() as $key =>  $field) {

            $id = Urlizer::urlize($field->getName());
            if(in_array($field->getType(), BaseFormField::SINGLE_CHOICE_FIELDS)){

                $formFieldData = $value->getFormFields()[$id];

                $choices = array_flip($field->getValueMap());

                $submittedValue = $formFieldData->getValue();

                if(!array_key_exists($submittedValue, $choices)){
                    continue;
                }
                $newValue = $choices[$submittedValue];

                $formFieldData->setValue($newValue);
                continue;
            }

            if(in_array($field->getType(), BaseFormField::MULTI_CHOICE_FIELDS)){
                $formFieldData = $value->getFormFields()[$id];
                $choices = array_flip($field->getValueMap());

                $submittedValue = $formFieldData->getValue();

                $newValue = [];
                foreach($submittedValue as $index){
                    if(!array_key_exists($index, $choices)){
                        continue;
                    }
                    $newValue[] = $choices[$index];
                }
                $formFieldData->setValue($newValue);
            }
        }
        return $value;
    }
}

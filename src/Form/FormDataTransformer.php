<?php

namespace Networking\FormGeneratorBundle\Form;

use Gedmo\Sluggable\Util\Urlizer;
use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Entity\FormData;
use Networking\FormGeneratorBundle\Entity\FormFieldData;
use Symfony\Component\Form\DataTransformerInterface;

class FormDataTransformer implements DataTransformerInterface
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct(Form $form){
        $this->form = $form;
    }

    /**
     * @param $value
     * @return FormData
     */
    public function transform($value){
        $value = new FormData();

        $value->setForm($this->form);
        foreach ($this->form->getFormFields() as $key =>  $field) {
            if(!$name = $field->getName()){
                $name = $field->getType().$key;
            }
            $id = Urlizer::urlize($name);

            $formFieldData = new FormFieldData();
            $formFieldData->setLabel($field->getFieldLabel());

            $value->addFormField($formFieldData, $id);

        }

        return $value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
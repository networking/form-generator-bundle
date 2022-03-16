<?php

namespace Networking\FormGeneratorBundle\Form;

use Gedmo\Sluggable\Util\Urlizer;
use Networking\FormGeneratorBundle\Model\BaseForm;
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

    protected $dataClass;

    protected $formFieldDataClass;

    public function __construct(BaseForm $form, $dataClass, $formFieldDataClass){
        $this->form = $form;
        $this->dataClass = $dataClass;
        $this->formFieldDataClass = $formFieldDataClass;
    }

    /**
     * @param $value
     * @return FormData
     */
    public function transform($value){

        $value = new $this->dataClass;

        $value->setForm($this->form);
        foreach ($this->form->getFormFields() as $key =>  $field) {
            if($field->getType() === 'Infotext'){
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
     * @return mixed
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
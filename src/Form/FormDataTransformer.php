<?php

declare(strict_types=1);

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

    public function __construct(BaseForm $form, protected $dataClass, protected $formFieldDataClass){
        $this->form = $form;
    }

    /**
     * @param $value
     */
    public function transform($value): FormData
    {

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
     */
    public function reverseTransform($value): mixed
    {
        return $value;
    }
}

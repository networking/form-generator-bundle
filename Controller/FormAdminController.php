<?php

namespace Networking\FormGeneratorBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Networking\FormGeneratorBundle\Entity\FormField;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @RouteResource("Form")
 */
class FormAdminController extends FOSRestController
{
    /**
     * @Route(requirements={"_format"="json|xml"})
     * @param Request $request
     * @return JsonResponse
     */
    public function cgetAction(Request $request)
    {
        throw new NotFoundHttpException('Action should not be used');
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request, $id)
    {
        if ($id) {
            $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
            /** @var Form $form */
            $form = $repo->find($id);
            if(!$form){
                throw new NotFoundHttpException('Form not found');
            }

            $form->setCollection();

            $view = $this->view($form);
            $view->setFormat('json');
            return $this->handleView($view);
        }
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @return Form
     */
    public function postAction(Request $request)
    {
        $view = $this->view(array(), 200);
        try {
            /** @var FormAdmin $admin */
            $admin = $this->get('networking_form_generator.admin.form');
            $form = $admin->getNewInstance();
            $form = $this->setFields($request, $form);

            $admin->create($form);
            $view->setData(array('id' => $form->getId(), 'message' => $this->get('translator')->trans('form_created',
                [], 'formGenerator')));
        } catch (\Exception $e) {
            $view = $this->view(array('message' => $e->getMessage()), 500);
        }

        $view->setFormat('json');
        return $this->handleView($view);
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @param $id
     * @return array|Form
     */
    public function putAction(Request $request, $id)
    {
        $view = $this->view(array(), 200);
        try {
            if ($id) {
                /** @var FormAdmin $admin */
                $admin = $this->get('networking_form_generator.admin.form');

                $form = $admin->getObject($id);
                if(!$form){
                    throw new NotFoundHttpException('Form not found');
                }

                $form->removeFields();
                $form = $this->setFields($request, $form);

                $validator = $this->get('validator');
                $errors = $validator->validate($form);

                if(count($errors) > 0){
                    $view = $this->view($errors, 500);
                }else{
                   $admin->update($form);
                    $view->setData(array('id' => $form->getId(), 'message' => 'Your form has been successfully updated'));
                }


            }
        } catch (\Exception $e) {
            $view = $this->view(array('message' => $e->getMessage()), 500);
        }

        $view->setFormat('json');
        return $this->handleView($view);
    }

    /**
     * @param Request $request
     *
     * @param Form $form
     * @return Form
     */
    protected function setFields(Request $request, Form $form)
    {
        $form->setName($request->get('name'));
        $form->setInfoText($request->get('infoText'));
        $form->setThankYouText($request->get('thankYouText'));
        $form->setEmail($request->get('email'));
        $form->setAction($request->get('action'));
        $form->setRedirect($request->get('redirect'));

        $collection = $request->get('collection');

        foreach ($collection as $field) {
            if (is_array($field)) {
                switch ($field['title']) {
                    case 'Multiple Radios':
                    case 'Multiple Checkboxes':
                    case 'Multiple Checkboxes Inline':
                    case 'Multiple Radios Inline':
                        $formField = new FormField();
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setName($field['fields']['name']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    case 'Legend':
                        $formField = new FormField();
                        $formField->setName($field['fields']['id']['value']);
                        $formField->setFieldLabel($field['fields']['name']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    default:
                        $formField = new FormField();
                        $formField->setName($field['fields']['id']['value']);
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                }
            }
        }
        return $form;
    }

    /**
     * @Route(requirements={"_format"="json|xml"}, defaults={"_format": "json"})
     * @param Request $request
     */
    public function deleteAction(Request $request, $id)
    {

        /** @var FormAdmin $admin */
        $admin = $this->get('networking_form_generator.admin.form');

        $form = $admin->getObject($id);
        if(!$form){
            throw new NotFoundHttpException('Form not found');
        }

        $admin->delete($form);
    }
}

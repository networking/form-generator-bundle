<?php

namespace Networking\FormGeneratorBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Networking\FormGeneratorBundle\Admin\FormFieldAdmin;
use Networking\FormGeneratorBundle\Entity\FormField;
use Networking\FormGeneratorBundle\Helper\FormHelper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @RouteResource("Form")
 */
class FormAdminController extends FOSRestController
{

    /**
     * @var AdminInterface
     */
    protected $admin;


    /**
     * FormAdminController constructor.
     * @param FormAdmin $formAdmin
     */
    public function __construct(FormAdmin $formAdmin)
    {
        $this->admin = $formAdmin;
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     */
    public function cgetAction()
    {
        throw new NotFoundHttpException('Action should not be used');
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function getAction(Request $request, $id)
    {
        if ($id) {
            $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
            /** @var Form $form */
            $form = $repo->find($id);
            if (!$form) {
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
     * @return Response
     */
    public function postAction(Request $request)
    {
        $view = $this->view([], 200);
        try {
            /** @var FormAdmin $admin */
           ;
            $form = $this->admin->getNewInstance();
            $form = $this->setFields($request, $form);

            $this->admin->create($form);
            $view->setData(['id' => $form->getId(), 'message' => $this->get('translator')->trans('form_created',
                [], 'formGenerator')]);
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
        }

        $view->setFormat('json');
        return $this->handleView($view);
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        $view = $this->view([], 200);
        try {
            if ($id) {

                $form = $this->admin->getObject($id);
                if (!$form) {
                    throw new NotFoundHttpException('Form not found');
                }

                $form->removeFields();
                $form = $this->setFields($request, $form);

                $validator = $this->get('validator');
                $errors = $validator->validate($form);

                if (count($errors) > 0) {
                    $view = $this->view($errors, 500);
                } else {
                    $this->admin->update($form);
                    $view->setData(['id' => $form->getId(), 'message' => 'Your form has been successfully updated']);
                }


            }
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
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
        if (!$form) {
            throw new NotFoundHttpException('Form not found');
        }

        $admin->delete($form);
    }


    /*
     * deletes a single entry
     * */

    public function deleteFormEntryAction(Request $request, $id, $rowid)
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NetworkingFormGeneratorBundle:FormData');

        $formData = $repo->find($rowid);
        $em->remove($formData);
        $em->flush();
        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);

    }


    public function deleteAllFormEntryAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NetworkingFormGeneratorBundle:FormData');

        $formData = $repo->findBy(['form' => $id]);
        foreach($formData as $record)
        {
            $em->remove($record);
            $em->flush();
        }



        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    /*
     * exports Excel File with the data
     * */
    public function excelExportAction(Request $request, $id)
    {

        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        /** @var Form $form */
        $form = $repo->find($id);
        $formFields = $form->getFormFields();
        $formData = $form->getFormData();


        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("initCms")
            ->setTitle("Export")
            ->setSubject("Export");


        $col = 'A';
        $row = '1';
        //Titel-Zeile ausgeben
        foreach($formFields as $key => $field){
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col.$row,$field->getFieldLabel());
            $col++;
        }
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col.$row,'Date');

        //Daten ausgeben
        foreach ($formData as $rowData) {
            $col = 'A';
            $row++;
            $formFields =  $rowData->getFormFields();
            foreach($formFields as $field)
            {
                $value = $field->getValue();
                if(is_array($value)){ $value = implode(" ",$value); }
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col.$row, $value);
                $col++;
            }
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col.$row, $rowData->getCreatedAt());
        }

        $phpExcelObject->getActiveSheet()->setTitle('export');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'form-export-'.date('Y-m-d').'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;

    }


    public function getAddressFieldArray()
    {
        $array = array();
        $array['Sprache'] = 'language';
        $array['Anrede'] = 'salutation';
        $array['Vorname'] = 'firstname';
        $array['Name'] = 'name';
        $array['Organisation'] = 'organisation';
        $array['Abteilung'] = 'departement';
        $array['Funktion'] = 'function';
        $array['Adresse 1'] = 'street1';
        $array['Adresse 2'] = 'street2';
        $array['PLZ'] = 'zip';
        $array['Ort'] = 'city';
        $array['Land'] = 'country';
        $array['Telefon'] = 'tel';
        $array['Mobile Telefon'] = 'mobile';
        $array['E-Mail'] = 'email';
        $array['URL'] = 'url';
        $array['Adresse 1 (privat)'] = 'street1Private';
        $array['Adresse 2 (privat)'] = 'street2Private';
        $array['PLZ (privat)'] = 'zipPrivate';
        $array['Ort (privat)'] = 'cityPrivate';
        $array['Land (privat)'] = 'countryPrivate';
        $array['Telefon (privat)'] = 'phonePrivate';
        $array['Mobile Telefon (privat)'] = 'mobilePrivate';
        $array['E-Mail (privat)'] = 'emailPrivate';
        $array['Korrespondenzadresse'] = 'corresponcende';
        $array['Kommentar'] = 'comment';
        return $array;
    }


    public function addressConfigAction(Request $request, $id)
    {
        $param = array();
        $data = array();
        $showForm = true;
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        /** @var Form $form */
        $formEntity = $repo->find($id);
        $formFields = $formEntity->getFormFields();
        $addressFieldArray = $this->getAddressFieldArray();
        $message = 'Bitte mappen Sie die Felder vom Formular mit den Feldern aus der Adress-Datenbank.';

        $formBuilder = $this->createFormBuilder();
        foreach ($formFields as $formField) {
            $formBuilder->add($formField->getId(), ChoiceType::class, array(
                'choices' =>  $addressFieldArray,
                'label' => $formField->getFieldLabel(),
                'data' => $formField->getMapping(),
                'required' => false));
        }
        $formBuilder->add('send', SubmitType::class, array('label' => 'Speichern'));
        $form = $formBuilder->getForm();



        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            //mapping speichern
            foreach ($formFields as $formField)
            {
                //formular daten in mapping feld speichern
                if($data[$formField->getId()] != '')
                {   $formField->setMapping($data[$formField->getId()]);
                }else
                {   $formField->setMapping(NULL);
                }
                $em->persist($formField);
                $em->flush();
            }
            $message = 'Daten wurden gespeichert.';
            $showForm = false;
        }




        $param['action'] = 'address_config';
        $param['data'] = $data;
        $param['formObject'] = $formEntity;
        $param['form'] = $form->createView();
        $param['message'] = $message;
        $param['showForm'] = $showForm;

        return $this->render(
            'NetworkingFormGeneratorBundle:Admin:addressConfig.html.twig',$param
        );
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function copyAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        $em = $this->getDoctrine()->getManager();
        /** @var Form $form */
        $form = $repo->find($id);

        if (!$form) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($request->getMethod() == 'POST') {

            try {
                /** @var Form $formCopy */
                $formCopy = clone $form;

                foreach ($form->getFormFields()->toArray() as $field){
                    $fieldCopy = clone $field;
                    $formCopy->addFormField($fieldCopy);
                }

                $status = 'success';
                $message = $this->admin->trans(
                    'message.copy_saved',
                    ['%page%' => $formCopy]
                );
                $em->persist($formCopy);
                $em->flush();

            } catch (\Exception $e) {
                $status = 'error';
                $message = $e->getMessage();
            }

            $this->admin->createObjectSecurity($formCopy);

            $this->get('session')->getFlashBag()->add(
                'sonata_flash_' . $status,
                $message
            );

            $request->getSession()->set('Page.last_edited', $formCopy->getId());

            return $this->redirect($this->admin->generateUrl('list'));
        }

        return $this->render(
            'NetworkingFormGeneratorBundle:Admin:copy.html.twig',
            [
                'action' => 'copy',
                'form' => $form,
                'id' => $id,
                'admin' => $this->admin
            ]
        );
    }

    /**
     * Returns true if the request is a XMLHttpRequest.
     *
     * @return bool True if the request is an XMLHttpRequest, false otherwise
     */
    protected function isXmlHttpRequest()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $request->isXmlHttpRequest() || $request->get('_xml_http_request');
    }

    /**
     * Returns the base template name.
     *
     * @return string The template name
     */
    protected function getBaseTemplate()
    {
        if ($this->isXmlHttpRequest()) {
            return $this->admin->getTemplate('ajax');
        }

        return $this->admin->getTemplate('layout');
    }

    /**
     * {@inheritdoc}
     */
    public function render($view, array $parameters = [], Response $response = null)
    {
        $parameters['admin'] = isset($parameters['admin']) ?
            $parameters['admin'] :
            $this->admin;

        $parameters['base_template'] = isset($parameters['base_template']) ?
            $parameters['base_template'] :
            $this->getBaseTemplate();

        $parameters['admin_pool'] = $this->get('sonata.admin.pool');

        return parent::render($view, $parameters, $response);
    }
}

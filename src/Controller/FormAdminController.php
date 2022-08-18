<?php

namespace Networking\FormGeneratorBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\Form;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormAdminController extends AbstractFOSRestController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @param FormAdmin $formAdmin
     * @param TranslatorInterface $translator
     * @param ValidatorInterface $validator
     */
    public function __construct(FormAdmin $formAdmin, TranslatorInterface $translator, ValidatorInterface $validator)
    {
        $this->admin = $formAdmin;
        $this->translator = $translator;
        $this->validator = $validator;
    }


    /**
     *
     * @Rest\Get(path="/{id}", requirements={"_format"="json|xml"}, defaults={"id": "0"})
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function getAction(Request $request, $id)
    {
        if ($id) {
            $repo = $this->getDoctrine()->getRepository($this->getParameter('networking_form_generator.form_class'));
            /** @var Form $form */
            $form = $repo->find($id);
            if (!$form) {
                throw new NotFoundHttpException('Form not found');
            }

            $form->setCollection();

            $view = $this->view([
                'name' => $form->getName(),
                'id' => $form->getId(),
                'collection' => $form->getCollection(),
                'action' => $form->getAction(),
                'email' => $form->getAction(),
            ]);
            $view->setFormat('json');

            return $this->handleView($view);
        }
    }

    /**
     * @Rest\Post(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $view = $this->view([], 200);
        try {
            /** @var FormAdmin $admin */
            $form = $this->admin->getNewInstance();
            $adminForm = $this->setupAdminForm($request, $form);
            $view = $this->processForm($request, $adminForm, 'create');
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
        }

        $view->setFormat('json');

        return $this->handleView($view);
    }

    /**
     * @Rest\Put(path="/{id}", requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {

        try {
            if ($id) {
                /** @var BaseForm $form */
                $form = $this->admin->getObject($id);

                if (!$form) {
                    throw new NotFoundHttpException('Form not found');
                }

                $request->setMethod('POST');
                $adminForm = $this->setupAdminForm($request, $form);
                $view = $this->processForm($request, $adminForm, 'update');
            }
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
        }

        $view->setFormat('json');

        return $this->handleView($view);
    }

    /**
     * @param BaseForm $form
     * @return \Symfony\Component\Form\FormInterface|null
     */
    protected function setupAdminForm(Request $request, BaseForm $form)
    {
        $this->admin->setUniqid($request->get('uniqid'));
        $this->admin->setSubject($form);
        $adminForm = $this->admin->getForm();
        $adminForm->setData($form);

        return $adminForm;
    }

    /**
     * @param Request $request
     * @param FormInterface $adminForm
     * @param $action
     * @return \FOS\RestBundle\View\View
     * @throws \Sonata\AdminBundle\Exception\LockException
     * @throws \Sonata\AdminBundle\Exception\ModelManagerThrowable
     */
    protected function processForm(Request $request, FormInterface $adminForm, $action = 'create')
    {
        $adminForm->handleRequest($request);
        /** @var BaseForm $data */
        $data = $adminForm->getData();
        if ($adminForm->isSubmitted() && $adminForm->isValid()) {
           
            if ($action === 'update') {
                $data->removeFields();
                $data = $this->setFields($request, $data);
                $this->admin->update($data);
            }
            if ($action === 'create') {
                $data = $this->setFields($request, $data);
                $this->admin->create($data);
            }

            $message = $action === 'create' ? 'form_created' : 'form_updated';

            return $this->view(['id' => $data->getId(), 'message' => $this->admin->trans($message)], 200);
        }
        $errors = $this->validator->validate($data);

        return $this->view($errors, 500);
    }

    /**
     * @param Request $request
     * @param Form $form
     *
     * @return Form
     */
    protected function setFields(Request $request, BaseForm $form)
    {

        $collection = $request->request->get('collection');



        $formFieldClass = $this->getParameter('networking_form_generator.form_field_class');


        foreach ($collection as $key => $field) {

            $formField = new $formFieldClass;
            if (is_array($field)) {

                switch ($field['title']) {
                    case 'Multiple Radios':
                    case 'Multiple Checkboxes':
                    case 'Multiple Checkboxes Inline':
                    case 'Multiple Radios Inline':
                        $field['fields']['id']['value'] = $field['fields']['id']['value'] ?: uniqid(
                            substr($field['fields']['label']['value'], 0, 3)
                        );
                        $formField->setName($field['fields']['id']['value']);
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    case 'Legend':
                        $field['fields']['id']['value'] = $field['fields']['id']['value'] ?: uniqid(
                            substr($field['fields']['name']['value'], 0, 3)
                        );
                        $formField->setName($field['fields']['id']['value']);
                        $formField->setFieldLabel($field['fields']['name']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    case 'Infotext':
                        $formField->setName(uniqid('info_text'));
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    default:
                        $field['fields']['id']['value'] = $field['fields']['id']['value'] ?: uniqid(
                            substr($field['fields']['label']['value'], 0, 3)
                        );
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
     * @Rest\Delete(path="/{id}", requirements={"_format"="json|xml"}, defaults={"_format": "json"})
     *
     * @param Request $request
     */
    public function deleteAction(Request $request, $id)
    {

        /** @var FormAdmin $admin */
        $admin = $this->get('Networking\FormGeneratorBundle\Admin\FormAdmin');

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
        $repo = $em->getRepository($this->getParameter('networking_form_generator.form_data_class'));

        $formData = $repo->find($rowid);
        $em->remove($formData);
        $em->flush();

        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    public function deleteAllFormEntryAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository($this->getParameter('networking_form_generator.form_data_class'));

        $formData = $repo->findBy(['form' => $id]);
        foreach ($formData as $record) {
            $em->remove($record);
            $em->flush();
        }

        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return StreamedResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function excelExportAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository($this->getParameter('networking_form_generator.form_class'));
        /** @var Form $form */
        $form = $repo->find($id);
        $formFields = $form->getFormFields();
        $formData = $form->getFormData();
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()->setCreator('initCms')
            ->setTitle('Export')
            ->setSubject('Export');

        $col = 'A';
        $row = '1';
        //Titel-Zeile ausgeben
        foreach ($formFields as $key => $field) {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, $field->getFieldLabel());
            ++$col;
        }
        $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, 'Date');

        //Daten ausgeben
        foreach ($formData as $rowData) {
            $col = 'A';
            ++$row;
            $formFields = $rowData->getFormFields();
            foreach ($formFields as $field) {
                $value = $field->getValue();
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, $value);
                ++$col;
            }
            $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, $rowData->getCreatedAt());
        }

        $spreadsheet->getActiveSheet()->setTitle('export');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // create the writer

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        // create the response
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            []
        );

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'form-export-'.date('Y-m-d').'.xlsx'
        );

        $response->headers->set(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8'
        );
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     *
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

                foreach ($form->getFormFields()->toArray() as $field) {
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
                'sonata_flash_'.$status,
                $message
            );

            $request->getSession()->set('Page.last_edited', $formCopy->getId());

            return $this->redirect($this->admin->generateUrl('list'));
        }

        return $this->renderWithExtraParams(
            '@NetworkingFormGenerator/Admin/copy.html.twig',
            [
                'action' => 'copy',
                'form' => $form,
                'id' => $id,
                'admin' => $this->admin,
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
     * @param $view
     * @param array $parameters
     * @param Response|null $response
     *
     * @return Response
     * @throws \Twig\Error\Error
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderWithExtraParams($view, array $parameters = [], Response $response = null)
    {
        $parameters['admin'] = isset($parameters['admin']) ?
            $parameters['admin'] :
            $this->admin;

        $parameters['base_template'] = isset($parameters['base_template']) ?
            $parameters['base_template'] :
            $this->getBaseTemplate();

        $parameters['admin_pool'] = ''; //$this->get('sonata.admin.pool');

        return $this->renderTemplate($view, $parameters, $response);
    }

    /**
     * @param $view
     * @param array $parameters
     * @param Response|null $response
     *
     * @return Response
     *
     * @throws \Twig\Error\Error
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderTemplate($view, array $parameters = [], Response $response = null)
    {
        if ($this->container->has('templating')) {
            $content = $this->container->get('templating')->render($view, $parameters);
        } elseif ($this->container->has('twig')) {
            $content = $this->container->get('twig')->render($view, $parameters);
        } else {
            throw new \LogicException(
                'You can not use the "render" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".'
            );
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }
}

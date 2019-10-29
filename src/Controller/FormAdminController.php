<?php

namespace Networking\FormGeneratorBundle\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Networking\FormGeneratorBundle\Entity\FormField;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @RouteResource("Form")
 */
class FormAdminController extends AbstractFOSRestController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * FormAdminController constructor.
     *
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
     *
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
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $view = $this->view([], 200);
        try {
            /** @var FormAdmin $admin */
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
     *
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
                    $view->setData(['id' => $form->getId(), 'message' => $this->get('translator')->trans('form_updated',
                        [], 'formGenerator')]);
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
     * @param Form    $form
     *
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


        foreach ($collection as $key =>  $field) {
            if (is_array($field)) {

                switch ($field['title']) {
                    case 'Multiple Radios':
                    case 'Multiple Checkboxes':
                    case 'Multiple Checkboxes Inline':
                    case 'Multiple Radios Inline':
                        $field['fields']['name']['value'] = $field['fields']['name']['value']?:uniqid(substr($field['fields']['label']['value'], 0,3));
                        $formField = new FormField();
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setName($field['fields']['name']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    case 'Legend':
                        $field['fields']['id']['value'] = $field['fields']['id']['value']?:uniqid(substr($field['fields']['name']['value'], 0,3));
                        $formField = new FormField();
                        $formField->setName($field['fields']['id']['value']);
                        $formField->setFieldLabel($field['fields']['name']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    case 'Infotext':
                        $formField = new FormField();
                        $formField->setName(uniqid('info_text'));
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setType($field['title']);
                        $formField->setOptions($field['fields']);
                        $form->addFormField($formField);
                        break;
                    default:
                        $field['fields']['id']['value'] = $field['fields']['id']['value']?:uniqid(substr($field['fields']['label']['value'], 0,3));
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
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
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

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
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

        $parameters['admin_pool'] = $this->get('sonata.admin.pool');

        return $this->renderTemplate($view, $parameters, $response);
    }

    /**
     * @param $view
     * @param array         $parameters
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
            throw new \LogicException('You can not use the "render" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        if (null === $response) {
            $response = new Response();
        }

        $response->setContent($content);

        return $response;
    }
}

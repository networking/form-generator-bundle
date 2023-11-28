<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Gedmo\Sluggable\Util\Urlizer;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormField;
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
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(FormAdmin $formAdmin, TranslatorInterface $translator, ValidatorInterface $validator, ManagerRegistry $registry)
    {
        $this->admin = $formAdmin;
        $this->translator = $translator;
        $this->validator = $validator;
        $this->registry = $registry;
    }


//    #[Rest\Get(path: "/{id}", requirements: ["_format" => "json|xml", "id" => "\d+"])]
//    public function getAction(Request $request, $id): Response
//    {
//
//        $repo = $this->registry->getRepository($this->getParameter('networking_form_generator.form_class'));
//        /** @var Form $form */
//        $form = $repo->find($id);
//        if (!$form) {
//            throw new NotFoundHttpException('Form not found');
//        }
//
//
//        $view = $this->view([
//            'name' => $form->getName(),
//            'id' => $form->getId(),
//            'collection' => $form->getCollection(),
//            'action' => $form->getAction(),
//            'email' => $form->getAction(),
//            'objectId' => $objectId,
//        ]);
//        $view->setFormat('json');
//
//        return $this->handleView($view);
//
//    }

    #[Rest\Post(path: "/", requirements: ["_format" => "json|xml"])]
    public function postAction(Request $request): Response
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
     * @param $id
     * @return Response
     */
    #[Rest\Put(path: "/{id}", requirements: ["_format" => "json|xml", "id" => "\d+"])]
    public function putAction(Request $request, $id): Response
    {

        $view = null;

        try {
            if ($id) {
                /** @var BaseForm $form */
                $form = $this->admin->getObject($id);
                if (!$form) {
                    throw new NotFoundHttpException('Form not found');
                }
                $adminForm = $this->setupAdminForm($request, $form);
                $view = $this->processForm($request, $adminForm, 'update');
            }
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
        }

        $view->setFormat('json');

        return $this->handleView($view);
    }

    protected function setupAdminForm(Request $request, BaseForm $form): ?\Symfony\Component\Form\FormInterface
    {
        $this->admin->setUniqid($request->get('uniqid'));
        $this->admin->setSubject($form);
        $adminForm = $this->admin->getForm();
        $adminForm->setData($form);

        return $adminForm;
    }

    /**
     * @param $action
     * @return \FOS\RestBundle\View\View
     * @throws \Sonata\AdminBundle\Exception\LockException
     * @throws \Sonata\AdminBundle\Exception\ModelManagerThrowable
     */
    protected function processForm(Request $request, FormInterface $adminForm, $action = 'create'): \FOS\RestBundle\View\View
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

            return $this->view(['id' => $data->getId(), 'message' => $this->translator->trans($message, [], $this->admin->getTranslationDomain())], 200);
        }
        $errors = $this->validator->validate($data);

        return $this->view($errors, 500);
    }

    /**
     * @param Form $form
     * @return Form
     */
    protected function setFields(Request $request, BaseForm $form): BaseForm
    {

        $collectionJson = $request->request->get('collection');

        $collection = json_decode($collectionJson, true);



        $formFieldClass = $this->getParameter('networking_form_generator.form_field_class');


        foreach ($collection as $key => $field) {

            /** @var BaseFormField $formField */
            $formField = new $formFieldClass;
            if (is_array($field)) {

                $uniqIdField = !array_key_exists('label', $field)?'name':'label';

                $uniqId = uniqid(substr(Urlizer::transliterate($field[$uniqIdField]), 0, 3));

                if(!array_key_exists('id', $field)){
                    $field['id'] =  $uniqId;
                }

                $formField->setName($field['id']);
                $formField->setFieldLabel($field['value']);
                $formField->setType($field['type']);
                $formField->setOptions($field['config']);

                $form->addFormField($formField);
            }
        }

        return $form;
    }

    #[Rest\Delete(path: "/{id}", requirements: ["_format" => "json|xml", "id" => "\d+"], defaults: ["_format" => "json"])]
    public function deleteAction(Request $request, $id): void
    {

        /** @var FormAdmin $admin */
        $admin = $this->get(\Networking\FormGeneratorBundle\Admin\FormAdmin::class);

        $form = $admin->getObject($id);
        if (!$form) {
            throw new NotFoundHttpException('Form not found');
        }

        $admin->delete($form);
    }


    public function deleteFormEntryAction(Request $request, $id, $rowid)
    {
        $em = $this->registry->getManager();
        $repo = $em->getRepository($this->getParameter('networking_form_generator.form_data_class'));

        $formData = $repo->find($rowid);
        $em->remove($formData);
        $em->flush();

        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    public function deleteAllFormEntryAction(Request $request, $id)
    {
        $em = $this->registry->getManager();
        $repo = $em->getRepository($this->getParameter('networking_form_generator.form_data_class'));

        $formData = $repo->findBy(['form' => $id]);
        foreach ($formData as $record) {
            $em->remove($record);
            $em->flush();
        }

        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    /**
     * @param $id
     *
     * @return StreamedResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function excelExportAction(Request $request, $id)
    {
        $repo = $this->registry->getRepository($this->getParameter('networking_form_generator.form_class'));
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function copyAction(Request $request, $id): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $repo = $this->registry->getRepository($this->getParameter('networking_form_generator.form_class'));
        $em = $this->registry->getManager();
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
                $message = $this->admin->getTranslator()->trans(
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

            $request->getSession()->getFlashBag()->add(
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

        return $request->isXmlHttpRequest() || $request->query->get('_xml_http_request');
    }

    /**
     * Returns the base template name.
     *
     * @return string The template name
     */
    protected function getBaseTemplate()
    {
        if ($this->isXmlHttpRequest()) {
            return $this->admin->getTemplateRegistry()->getTemplate('ajax');
        }

        return $this->admin->getTemplateRegistry()->getTemplate('layout');
    }

    /**
     * @param $view
     * @param Response|null $response
     *
     * @return Response
     * @throws \Twig\Error\Error
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderWithExtraParams($view, array $parameters = [], Response $response = null)
    {
        $parameters['admin'] ??= $this->admin;

        $parameters['base_template'] ??= $this->getBaseTemplate();

        $parameters['admin_pool'] = ''; //$this->get('sonata.admin.pool');

        return $this->renderTemplate($view, $parameters, $response);
    }

    /**
     * @param $view
     * @param Response|null $response
     *
     * @return Response
     *
     * @throws \Twig\Error\Error
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
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

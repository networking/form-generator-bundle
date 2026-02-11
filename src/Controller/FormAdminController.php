<?php

declare(strict_types=1);

namespace Networking\FormGeneratorBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormField;
use Networking\FormGeneratorBundle\Model\Form;
use Networking\InitCmsBundle\Util\Urlizer;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Properties;
use OpenSpout\Writer\XLSX\Writer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FormAdminController extends AbstractController
{
    public function __construct(
        private readonly FormAdmin $admin,
        private readonly TranslatorInterface $translator,
        private readonly ValidatorInterface $validator,
        private readonly ManagerRegistry $registry,
    ) {
    }

    #[Route('/', name: 'networking_formgenerator_formadmin_post', methods: ['POST'])]
    public function postAction(Request $request): Response
    {
        try {
            /** @var FormAdmin $admin */
            $form = $this->admin->getNewInstance();
            $adminForm = $this->setupAdminForm($request, $form);

            return $this->processForm($request, $adminForm, 'create');
        } catch (\Exception|ModelManagerThrowable $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/{id}', name: 'networking_formgenerator_formadmin_put', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function putAction(Request $request, $id): Response
    {
        try {
            if ($id) {
                /** @var BaseForm $form */
                $form = $this->admin->getObject($id);
                if (!$form) {
                    throw new NotFoundHttpException('Form not found');
                }
                $adminForm = $this->setupAdminForm($request, $form);

                return $this->processForm($request, $adminForm, 'update');
            }
        } catch (\Exception|ModelManagerThrowable $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => 'Form not found'], Response::HTTP_NOT_FOUND);
    }

    protected function setupAdminForm(
        Request $request,
        BaseForm $form,
    ): ?FormInterface {
        $this->admin->setUniqid($request->query->get('uniqid'));
        $this->admin->setSubject($form);
        $adminForm = $this->admin->getForm();
        $adminForm->setData($form);

        return $adminForm;
    }

    /**
     * @throws \Sonata\AdminBundle\Exception\LockException
     * @throws ModelManagerThrowable
     */
    protected function processForm(
        Request $request,
        FormInterface $adminForm,
        $action = 'create',
    ): JsonResponse {
        $adminForm->handleRequest($request);
        /** @var BaseForm $data */
        $data = $adminForm->getData();

        if ($adminForm->isSubmitted() && $adminForm->isValid()) {
            if ('update' === $action) {
                $data->removeFields();
                $data = $this->setFields($request, $data);
                $this->admin->update($data);
            }
            if ('create' === $action) {
                $data = $this->setFields($request, $data);
                $this->admin->create($data);
            }

            $message = 'create' === $action ? 'form_created' : 'form_updated';

            return new JsonResponse([
                'id' => $data->getId(),
                'message' => $this->translator->trans(
                    $message,
                    [],
                    $this->admin->getTranslationDomain()
                ),
            ]);
        }
        $errors = $this->validator->validate($data);

        return new JsonResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    protected function setFields(Request $request, BaseForm $form): BaseForm
    {
        $collectionJson = $request->request->get('collection');

        $collection = json_decode($collectionJson, true);

        $formFieldClass = $this->getParameter(
            'networking_form_generator.form_field_class'
        );

        foreach ($collection as $key => $field) {
            /** @var BaseFormField $formField */
            $formField = new $formFieldClass();
            if (is_array($field)) {
                $uniqIdField = !array_key_exists('label', $field) ? 'name'
                  : 'label';

                $uniqId = uniqid(
                    substr(Urlizer::urlize($field[$uniqIdField]), 0, 3)
                );

                if (!array_key_exists('id', $field)) {
                    $field['id'] = $uniqId;
                }

                $formField->setName($field['id']);
                $formField->setFieldLabel($field['value']);
                $formField->setType($field['type']);
                $formField->setOptions($field['config']);
                if (array_key_exists('sortOrder', $field)) {
                    $formField->setPosition($field['sortOrder']);
                }

                if (array_key_exists('position', $field)) {
                    $formField->setPosition($field['position']);
                }

                if (null === $formField->getPosition()) {
                    $formField->setPosition($key);
                }

                $form->addFormField($formField);
            }
        }

        return $form;
    }

    public function deleteFormEntryAction(int $id, int $rowId): RedirectResponse
    {
        $em = $this->registry->getManager();
        $repo = $em->getRepository(
            $this->getParameter('networking_form_generator.form_data_class')
        );

        $formData = $repo->find($rowId);
        $em->remove($formData);
        $em->flush();

        return $this->redirectToRoute(
            'admin_networking_forms_show',
            ['id' => $id]
        );
    }

    public function deleteAllFormEntryAction(
        $id,
    ): RedirectResponse {
        $em = $this->registry->getManager();
        $repo = $em->getRepository(
            $this->getParameter('networking_form_generator.form_data_class')
        );

        $formData = $repo->findBy(['form' => $id]);
        foreach ($formData as $record) {
            $em->remove($record);
            $em->flush();
        }

        return $this->redirectToRoute(
            'admin_networking_forms_show',
            ['id' => $id]
        );
    }

    public function excelExportAction($id): StreamedResponse
    {
        $repo = $this->registry->getRepository(
            $this->getParameter('networking_form_generator.form_class')
        );
        /** @var Form $form */
        $form = $repo->find($id);
        $formFields = $form->getFormFields();
        $formData = $form->getFormData();

        $data = [];
        $header = [];
        foreach ($formFields as $field) {
            if (in_array($field->getType(), BaseFormField::NON_VALUE_FIELDS)) {
                continue;
            }
            $header[] = Cell::fromValue($field->getFieldLabel());
        }

        $header[] = Cell::fromValue('Datum');
        $data[] = $header;
        $dateStyle = new Style()->withFormat('dd.MM.yyyy H:mm:ss');
        foreach ($formData as $rowData) {
            $formFieldData = $rowData->getFormFields();

            $values = [];
            foreach ($formFieldData as $fieldData) {
                $value = $fieldData->getValue();
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }

                $values[] = Cell::fromValue($value);
            }

            $values[] = Cell::fromValue($rowData->getCreatedAt(), $dateStyle);
            $data[] = $values;
        }
        $properties = new Properties(
            title: 'form-export-'.date('Y-m-d'),
            creator: 'initCms'
        );
        $options = new Options(properties: $properties);
        $options->setColumnWidth(10);
        $writer = new Writer($options);
        $writer->openToBrowser('form-export-'.date('Y-m-d').'.xlsx');

        $response = new StreamedResponse(function () use ($writer, $data) {
            $writer->openToBrowser('filename.xlsx');

            foreach ($data as $cells) {
                $row = new Row($cells);
                $writer->addRow($row);
            }

            $writer->close();
        });
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');

        return $response;
    }

    public function copyAction(Request $request, $id): RedirectResponse|Response
    {
        $repo = $this->registry->getRepository(
            $this->getParameter('networking_form_generator.form_class')
        );
        $em = $this->registry->getManager();
        /** @var Form $form */
        $form = $repo->find($id);

        if (!$form) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ('POST' == $request->getMethod()) {
            try {
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
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function isXmlHttpRequest(): bool
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $request->isXmlHttpRequest()
          || $request->query->get(
              '_xml_http_request'
          );
    }

    protected function getBaseTemplate(): string
    {
        try {
            if ($this->isXmlHttpRequest()) {
                return $this->admin->getTemplateRegistry()->getTemplate('ajax');
            }
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface) {
        }

        return $this->admin->getTemplateRegistry()->getTemplate('layout');
    }

    public function renderWithExtraParams(
        $view,
        array $parameters = [],
        ?Response $response = null,
    ): ?Response {
        $parameters['admin'] ??= $this->admin;

        $parameters['base_template'] ??= $this->getBaseTemplate();

        $parameters['admin_pool'] = ''; // $this->get('sonata.admin.pool');

        return $this->renderTemplate($view, $parameters, $response);
    }

    public function renderTemplate(
        $view,
        array $parameters = [],
        ?Response $response = null,
    ): ?Response {
        if ($this->container->has('twig')) {
            $content = $this->container->get('twig')->render(
                $view,
                $parameters
            );
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

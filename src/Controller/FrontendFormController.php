<?php
/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Networking\FormGeneratorBundle\Form\FormType;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Networking\FormGeneratorBundle\Model\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Networking\FormGeneratorBundle\Helper\FormHelper;

class FrontendFormController extends AbstractController
{
    const FORM_DATA = 'application_networking_form_generator_form_data';
    const FORM_COMPLETE = 'application_networking_form_generator_form_complete';

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FormHelper
     */
    protected $formHelper;

    /**
     * @var string
     */
    protected $emailAddress;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * FrontendFormController constructor.
     * @param FormHelper $formHelper
     * @param $emailAddress
     */
    public function __construct(ManagerRegistry $registry, FormHelper $formHelper, $emailAddress)
    {
        $this->registry = $registry;
        $this->formHelper = $formHelper;
        $this->emailAddress = $emailAddress;
    }


    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function viewFormAction(Request $request, $id)
    {
        /** @var Form $form */
        $form = $this->registry->getRepository(Form::class)->find($id);
        if (!$form || !$form->isOnline()) {
            throw new NotFoundHttpException(sprintf('Form with id %s could not be found', $id));
        }
        $formType = $this->createForm(
            FormType::class, [],
            [
                'form' => $form,
                'data_class' => $this->getParameter('networking_form_generator.form_data_class'),
                'form_field_data_class' => $this->getParameter('networking_form_generator.form_field_data_class'),
                'frontend_css_input_sizes' => $this->getParameter('networking_form_generator.frontend_css_input_sizes')
            ]);

        $this->clearSessionVariables($request);
        $formType->handleRequest($request);



        $redirect = $request->headers->get('referer');

        if ($formType->isSubmitted()) {

            if ($formType->isValid()) {

                $data = $formType->getData();
                $this->setFormComplete($request, true);

                if ($form->isEmailAction()) {
                    $this->formHelper->sendEmail($form, $data, $this->emailAddress);
                }

                if ($form->isDbAction()) {
                    $this->formHelper->saveToDb($form, $data);
                }

                if ($form->getRedirect()) {
                    $request->getSession()->getFlashBag()->add('form_notice', $form->getThankYouText());
                    $redirect = $form->getRedirect();
                }
            } else {
                $this->setSubmittedFormData($request, $request->request->all($formType->getName()));
                $this->setFormComplete($request, false);
            }
        }

        $request->getSession()->set('no_cache', true);

        return new RedirectResponse($redirect . '#formgenerator_form_'.$form->getId());
    }

    /**
     * @param Form   $form
     * @param null   $actionUrl
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFormAction(Request $request, BaseForm $form, $actionUrl = null, $template = '@NetworkingFormGenerator/Form/form.html.twig', $options = [])
    {

        if(!$form->isOnline()){
            return new Response();
        }
        if (is_null($actionUrl)) {
            $actionUrl = $this->generateUrl('networking_form_view', ['id' => $form->getId()]);

        }
        $options =  [
                'form' => $form,
                'action' => $actionUrl,
                'data_class' => $this->getParameter('networking_form_generator.form_data_class'),
                'form_field_data_class' => $this->getParameter('networking_form_generator.form_field_data_class'),
                'frontend_css_input_sizes' => $this->getParameter('networking_form_generator.frontend_css_input_sizes')
            ];
        $options = array_merge($options, $options);
        $formType = $this->createForm(FormType::class, [], $options);

        $formData = $this->getSubmittedFormData($request);
        $formComplete = $this->getFormComplete($request);

        $this->clearSessionVariables($request);

        if (!empty($formData)) {
            $formType->submit($formData);
        }

        return $this->render($template,
            [
                'formComplete' => $formComplete,
                'formView' => $formType->createView(),
                'form' => $form,
            ]);
    }

    protected function clearSessionVariables(Request $request)
    {
        $request->getSession()->remove(self::FORM_DATA);
        $request->getSession()->remove(self::FORM_COMPLETE);
    }

    /**
     * @param $complete
     */
    protected function setFormComplete(Request $request, $complete)
    {
        $request->getSession()->set(self::FORM_COMPLETE, $complete);
    }

    /**
     * @return bool
     */
    protected function getFormComplete(Request $request)
    {
        return $request->getSession()->get(self::FORM_COMPLETE, false);
    }

    /**
     * @param $data
     */
    protected function setSubmittedFormData(Request $request, $data)
    {
        $request->getSession()->set(self::FORM_DATA, $data);
    }

    /**
     * @return array
     */
    protected function getSubmittedFormData(Request $request)
    {
        return $request->getSession()->get(self::FORM_DATA, []);
    }
}

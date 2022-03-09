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

use Networking\FormGeneratorBundle\Form\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Networking\FormGeneratorBundle\Entity\Form;
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

    protected $emailAddress;

    /**
     * FrontendFormController constructor.
     * @param FormHelper $formHelper
     * @param SessionInterface $session
     * @param $emailAddress
     */
    public function __construct(FormHelper $formHelper, SessionInterface $session, $emailAddress)
    {
        $this->formHelper = $formHelper;
        $this->session = $session;
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
        $form = $this->getDoctrine()->getRepository(Form::class)->find($id);
        if (!$form || !$form->isOnline()) {
            throw new NotFoundHttpException(sprintf('Form with id %s could not be found', $id));
        }

        $formType = $this->createForm(FormType::class, [], ['form' => $form]);

        $this->clearSessionVariables();
        $formType->handleRequest($request);

        $redirect = $request->headers->get('referer');

        if ($formType->isSubmitted()) {
            if ($formType->isValid()) {
                $data = $formType->getData();
                $this->setFormComplete(true);

                if ($form->isEmailAction()) {
                    $this->formHelper->sendEmail($form, $data, $this->emailAddress);
                }

                if ($form->isDbAction()) {
                    $this->formHelper->saveToDb($form, $data);
                }

                if ($form->getRedirect()) {
                    $this->session->getFlashBag()->add('form_notice', $form->getThankYouText());
                    $redirect = $form->getRedirect();
                }
            } else {
                $this->setSubmittedFormData($request->request->get($formType->getName()));
                $this->setFormComplete(false);
            }
        }

        $request->getSession()->set('no_cache', true);

        return new RedirectResponse($redirect);
    }

    /**
     * @param Form   $form
     * @param null   $actionUrl
     * @param string $template
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFormAction(Form $form, $actionUrl = null, $template = '@NetworkingFormGenerator/Form/form.html.twig', $options = [])
    {

        if(!$form->isOnline()){
            return new Response();
        }
        if (is_null($actionUrl)) {
            $actionUrl = $this->generateUrl('networking_form_view', ['id' => $form->getId()]);

        }
        $options = array_merge(['action' => $actionUrl, 'form' => $form], $options);
        $formType = $this->createForm(FormType::class, [], $options);

        $formData = $this->getSubmittedFormData();
        $formComplete = $this->getFormComplete();

        $this->clearSessionVariables();

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

    protected function clearSessionVariables()
    {
        $this->session->remove(self::FORM_DATA);
        $this->session->remove(self::FORM_COMPLETE);
    }

    /**
     * @param $complete
     */
    protected function setFormComplete($complete)
    {
        $this->session->set(self::FORM_COMPLETE, $complete);
    }

    /**
     * @return bool
     */
    protected function getFormComplete()
    {
        return $this->session->get(self::FORM_COMPLETE, false);
    }

    /**
     * @param $data
     */
    protected function setSubmittedFormData($data)
    {
        $this->session->set(self::FORM_DATA, $data);
    }

    /**
     * @return array
     */
    protected function getSubmittedFormData()
    {
        return $this->session->get(self::FORM_DATA, []);
    }
}

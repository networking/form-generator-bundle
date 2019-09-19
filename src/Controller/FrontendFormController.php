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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Networking\FormGeneratorBundle\Entity\Form;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Networking\FormGeneratorBundle\Helper\FormHelper;

class FrontendFormController extends Controller
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
     * FrontendFormController constructor.
     *
     * @param FormHelper       $formHelper
     * @param SessionInterface $session
     */
    public function __construct(FormHelper $formHelper, SessionInterface $session)
    {
        $this->formHelper = $formHelper;
        $this->session = $session;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
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
        if (!$form) {
            throw new NotFoundHttpException(sprintf('Form with id %s could not be found', $id));
        }

        $formType = $this->createForm(FormType::class, [], ['form' => $form]);

        $this->clearSessionVariables();
        $formType->handleRequest($request);

        $redirect = $request->headers->get('referer');

        if ($formType->isSubmitted()) {
            if ($formType->isValid()) {
                $data = $request->get($formType->getName());
                $this->setFormComplete(true);

                if ($form->isEmailAction()) {
                    $this->formHelper->sendEmail($form, $data, $this->container->getParameter('form_generator_from_email'));
                }

                if ($form->isDbAction()) {
                    $this->formHelper->saveToDb($form, $data);
                }

                //check if confirmation email needs to be send.
                $emailField = strtolower($form->getEmailField());
                $doubleOptIn = strtolower($form->getDoubleOptIn());

                if($emailField != '' and $doubleOptIn != 'yes'){
                    if(isset($data[$emailField]) and  filter_var($data[$emailField], FILTER_VALIDATE_EMAIL) ) {
                        $this->sendConfirmationEmail($data[$emailField], $this->container->getParameter('form_generator_from_email'), $form->getName(), $form->getThankYouText());
                    }
                }elseif($emailField != '' and $doubleOptIn == 'yes'){
                        //todo: double opt in ausloesen

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
    public function renderFormAction($form, $actionUrl = null, $template = '@NetworkingFormGenerator/Form/form.html.twig')
    {
        if (is_null($actionUrl)) {
            $actionUrl = $this->generateUrl('networking_form_view', ['id' => $form->getId()]);
        }
        $formType = $this->createForm(FormType::class, [], ['action' => $actionUrl, 'form' => $form]);

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



    /*
     * send confirmation email
     * **/
    public function sendConfirmationEmail($to, $from, $subject, $text )
    {
        $plaineText =  preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($text))) );

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->addTo($to)
            ->setFrom($from)
            ->setBody($plaineText)
            ->addPart($text, 'text/html');


        return $this->get('mailer')->send($message);
    }


}

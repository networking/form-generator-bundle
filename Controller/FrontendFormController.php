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


use Networking\FormGeneratorBundle\Entity\FormData;
use Networking\FormGeneratorBundle\Entity\FormFieldData;
use Networking\FormGeneratorBundle\Form\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->session = $this->container->get('session');
    }

    public function viewFormAction(Request $request, $id)
    {
        /** @var Form $form */
        $form = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form')->find($id);
        if (!$form) {
            throw new NotFoundHttpException(sprintf('Form with id %s could not be found', $id));
        }

        $formType = $this->createForm(new FormType(array()), array(), array('form' => $form));

        $this->clearSessionVariables();


        if ($request->getMethod() == 'POST') {
            $formType->handleRequest($request);

            if ($formType->isValid()) {
                /** @var FormHelper $formHelper */
                $formHelper = $this->get('networking_form_generator.helper.form');
                $data = $request->get($formType->getName());
                $this->setFormComplete(true);

                if ($form->isEmailAction()) {
                    $formHelper->sendEmail($form, $data, $this->container->getParameter
                    ('form_generator_from_email'));
                }

                if ($form->isDbAction()) {
                    $formHelper->saveToDb($form, $data);
                }

                if ($form->getRedirect()) {
                    $this->session->getFlashBag()->add('form_notice',$form->getThankYouText());
                    return $this->redirect($form->getRedirect());
                }
            } else {
                $this->setSubmittedFormData($request->request->get($formType->getName()));
                $this->setFormComplete(false);
            }
        }
        return $this->redirect($request->headers->get('referer')."#formAnswer");

    }

    /**
     * @param Form $form
     * @param null $actionUrl
     * @param string $template
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFormAction(Form $form, $actionUrl = null, $template =
    'NetworkingFormGeneratorBundle:Form:form.html.twig')
    {

        if(is_null($actionUrl)){
            $actionUrl = $this->generateUrl('networking_form_view', array('id' => $form->getId()));
        }
        $formType = $this->createForm(new FormType(array()), array(), array('action' => $actionUrl, 'form'  => $form));
        $formData = $this->getSubmittedFormData();
        $formComplete = $this->getFormComplete();

        $this->clearSessionVariables();

        if(!empty($formData)){
            $formType->submit($formData);
        }
        return $this->render($template,
            array(
                'formComplete' => $formComplete,
                'formView' => $formType->createView(),
                'form' => $form
            ));
    }

    /**
     *
     */
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
     * @return boolean
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
        return $this->session->get(self::FORM_DATA, array());
    }
} 
<?php
/**
 * This file is part of the sko  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\FormGeneratorBundle\Helper;


use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Entity\FormData;
use Networking\FormGeneratorBundle\Entity\FormFieldData;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\AbstractType;

class FormHelper
{

    /**
     * @var \Swift_Mailer
     */
    public $mailer;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var TwigEngine
     */
    protected $twig;

    public function __construct(\Swift_Mailer $mailer, Registry $doctrine, TwigEngine $twig)
    {
        $this->mailer = $mailer;
        $this->doctrine = $doctrine;
        $this->twig = $twig;

    }

    /**
     * Send an plain text email of the data
     *
     * @param Form $form
     * @param array $data
     * @param string $emailFrom
     * @return int
     */
    public function sendEmail(Form $form, array $data, $emailFrom = '')
    {

        $message = \Swift_Message::newInstance()
            ->setSubject($form->getName())
            ->setFrom($emailFrom)
            ->setBody(
                $this->renderView(
                    'NetworkingFormGeneratorBundle:Email:email.txt.twig',
                    array('data' => $data, 'form' => $form)
                )
            );
        foreach (explode(',', $form->getEmail()) as $email) {
            $message->addTo(trim($email));
        }
        return $this->mailer->send($message);

    }

    /**
     * Save form data to the DB
     *
     * @param Form $form
     * @param array $data
     * @param AbstractType $originalForm
     * @return bool
     */
    /**
     * @param Form $form
     * @param array $data
     * @param \Symfony\Component\Form\Form|null $originalForm
     * @return bool
     */
    public function saveToDb(Form $form, array $data, \Symfony\Component\Form\Form $originalForm = null)
    {
        $formData = new FormData();
        $formData->setForm($form);

        foreach ($data as $key => $val) {
            if($key == '_token') continue;
            $formFieldData = new FormFieldData();
            if ($field = $form->getField($key)) {
                $formFieldData->setLabel($field->getFieldLabel());
                $formFieldData->setFormFieldValue($field, $val);

            } else {
                if ($originalForm) {
                    $field = $originalForm->get($key);
                    $label =$field->get('label');
                    $formFieldData->setLabel($label);
                    $formFieldData->setValue($val);
                }

            }
            $formData->addFormField($formFieldData);
        }

        $em = $this->doctrine->getManager();
        $em->persist($formData);
        $em->flush();

    }

    /**
     * Returns a rendered view.
     *
     * @param string $view The view name
     * @param array $parameters An array of parameters to pass to the view
     *
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = array())
    {
        return $this->twig->render($view, $parameters);
    }
} 
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

use Doctrine\Persistence\ManagerRegistry;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormData;
use Networking\FormGeneratorBundle\Model\Form;
use Networking\FormGeneratorBundle\Model\FormData;
use Twig\Environment;

class FormHelper
{
    /**
     * @var \Swift_Mailer
     */
    public $mailer;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * FormHelper constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param ManagerRegistry $doctrine
     * @param Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, ManagerRegistry $doctrine, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->doctrine = $doctrine;
        $this->twig = $twig;
    }

    /**
     * Send an plain text email of the data.
     *
     * @param Form $form
     * @param BaseFormData $formData
     * @param string $emailFrom
     *
     * @return int
     */
    public function sendEmail(BaseForm $form, BaseFormData $formData, $emailFrom = '')
    {
        //https://stackoverflow.com/questions/45447972/attempted-to-call-an-undefined-method-named-newinstance-of-class-swift-messag

        $message = (new \Swift_Message($form->getName()));
        $message->setFrom($emailFrom);
        $messageText = $this->renderView(
            'NetworkingFormGeneratorBundle:Email:email.txt.twig',
            ['formData' => $formData]
        );

        $message->setBody($messageText);

        foreach (explode(',', $form->getEmail()) as $email) {
            $message->addTo(trim($email));
        }

        return $this->mailer->send($message);
    }

    /**
     * Save form data to the DB.
     *
     * @param Form $form
     * @param array $data
     * @param \Symfony\Component\Form\Form|null $originalForm
     */
    public function saveToDb(BaseForm $form, BaseFormData $formData)
    {

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
    public function renderView($view, array $parameters = [])
    {
        return $this->twig->render($view, $parameters);
    }
}

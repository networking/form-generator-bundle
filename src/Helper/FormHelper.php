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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;

class FormHelper
{
    /**
     * @var MailerInterface
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
     * @param MailerInterface $mailer
     * @param ManagerRegistry $doctrine
     * @param Environment $twig
     */
    public function __construct(MailerInterface $mailer, ManagerRegistry $doctrine, Environment $twig)
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
        $messageText = $this->renderView(
            '@NetworkingFormGenerator/Email/email.txt.twig',
            ['formData' => $formData]
        );

        $email = (new Email())
            ->from($emailFrom)
            ->subject($form->getName())
            ->text($messageText);

        foreach (explode(',', $form->getEmail()) as $emailAddress) {
            $email->addTo(trim($emailAddress));
        }

        return $this->mailer->send($email);


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

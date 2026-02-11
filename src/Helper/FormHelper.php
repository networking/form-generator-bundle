<?php

declare(strict_types=1);

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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\Error;


readonly class FormHelper
{
    public function __construct(
        private MailerInterface $mailer,
        private ManagerRegistry $doctrine,
        private Environment $twig)
    {
    }

    public function sendEmail(BaseForm $form, BaseFormData $formData, string $emailFrom = ''): bool
    {
        try {
            $messageText = $this->twig->render(
                '@NetworkingFormGenerator/Email/email.txt.twig',
                ['formData' => $formData]
            );

            $email = new Email()
              ->from($emailFrom)
              ->subject($form->getName())
              ->text($messageText);

            foreach (explode(',', (string) $form->getEmail()) as $emailAddress) {
                $email->addTo(trim($emailAddress));
            }
            $this->mailer->send($email);

            return true;
        } catch (Error|TransportExceptionInterface) {
            return false;
        }
    }

    public function saveToDb(BaseFormData $formData): void
    {
        $em = $this->doctrine->getManager();
        $em->persist($formData);
        $em->flush();
    }
}

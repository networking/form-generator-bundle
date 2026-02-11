<?php

namespace Networking\FormGeneratorBundle\Twig\Components;

use Doctrine\Persistence\ManagerRegistry;
use Networking\FormGeneratorBundle\Form\FormType;
use Networking\FormGeneratorBundle\Helper\FormHelper;
use Networking\FormGeneratorBundle\Model\BaseForm;
use Networking\FormGeneratorBundle\Model\BaseFormData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent()]
final class FormPageContent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public bool $formComplete = false;

    public bool $isAdmin = false;

    public ?BaseForm $formObject = null;

    public ?BaseFormData $initialFormData = null;

    #[LiveProp]
    public int $formId;

    #[ExposeInTemplate]
    public bool $submitted = false;

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly FormHelper $formHelper,
        private readonly RouterInterface $router,
        private readonly FormFactoryInterface $formFactory,
        #[Autowire(param: 'networking_form_generator.from_email')]
        private readonly string $emailAddress,
        #[Autowire(param: 'networking_form_generator.form_class')]
        private readonly string $formClass,
        #[Autowire(param: 'networking_form_generator.form_data_class')]
        private readonly string $formDataClass,
        #[Autowire(param: 'networking_form_generator.form_field_data_class')]
        private readonly string $formFieldDataClass,
        #[Autowire(param: 'networking_form_generator.frontend_css_input_sizes')]
        private readonly array $frontendCssInputSizes,
    ) {
    }


    #[LiveAction]
    public function save(): ?RedirectResponse
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        $data = $this->getForm()->getData();

        if ($this->formObject->isEmailAction()) {
            $this->formHelper->sendEmail($this->formObject, $data, $this->emailAddress);
        }

        if ($this->formObject->isDbAction()) {
            $this->formHelper->saveToDb($data);
        }

        if ($this->formObject->getRedirect()) {
            $this->addFlash('form_notice', $this->formObject->getThankYouText());
            $redirect = $this->formObject->getRedirect();

            return $this->redirect($redirect);
        }
        $this->formComplete = true;

        return null;
    }

    /**
     * @throws \Exception
     */
    protected function instantiateForm(): FormInterface
    {

        if (!$this->formObject) {
            $object = $this->registry->getRepository($this->formClass)->find($this->formId);

            if (!$object instanceof BaseForm) {
                throw new \Exception('Form not found');
            }

            $this->formObject = $object;
        }

        if (!$this->formObject->isOnline()) {
            throw new \Exception('Form is not online');
        }

        $options = [
            'is_admin' => $this->isAdmin,
            'form' => $this->formObject,
            'data_class' => $this->formDataClass,
            'form_field_data_class' => $this->formFieldDataClass,
            'frontend_css_input_sizes' => $this->frontendCssInputSizes,
        ];
        $options = [...$options, ...$options];

        return $this->createForm(FormType::class, $this->initialFormData, $options);
    }
}

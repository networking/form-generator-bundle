<?php

namespace Networking\FormGeneratorBundle\Controller;

use Application\Networking\InitCmsBundle\ApplicationNetworkingInitCmsBundle;
use FOS\RestBundle\Controller\FOSRestController;
use Networking\FormGeneratorBundle\Entity\FormField;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Networking\FormGeneratorBundle\Entity\Form;
use Networking\FormGeneratorBundle\Admin\FormAdmin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Application\Networking\InitCmsBundle\Entity\Address;
use Application\Networking\InitCmsBundle\Entity\AddressCategory;



use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * @RouteResource("Form")
 */
class FormAdminController extends FOSRestController
{
    /**
     * @var AdminInterface
     */
    protected $admin;

    /**
     * FormAdminController constructor.
     *
     * @param FormAdmin $formAdmin
     */
    public function __construct(FormAdmin $formAdmin)
    {
        $this->admin = $formAdmin;
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     */
    public function cgetAction()
    {
        throw new NotFoundHttpException('Action should not be used');
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function getAction(Request $request, $id)
    {
        if ($id) {
            $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
            /** @var Form $form */
            $form = $repo->find($id);
            if (!$form) {
                throw new NotFoundHttpException('Form not found');
            }

            $form->setCollection();

            $view = $this->view($form);
            $view->setFormat('json');

            return $this->handleView($view);
        }
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postAction(Request $request)
    {
        $view = $this->view([], 200);
        try {
            /** @var FormAdmin $admin */
            $form = $this->admin->getNewInstance();
            $oldFormFields = array(); //$this->oldFieldsToArray($form->getFormFields());
            $form = $this->setFields($request, $form, $oldFormFields);

            $this->admin->create($form);
            $view->setData(['id' => $form->getId(), 'message' => $this->get('translator')->trans('form_created',
                [], 'formGenerator')]);
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
        }

        $view->setFormat('json');

        return $this->handleView($view);
    }

    /**
     * @Route(requirements={"_format"="json|xml"})
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        $view = $this->view([], 200);
        try {
            if ($id) {
                $form = $this->admin->getObject($id);
                if (!$form) {
                    throw new NotFoundHttpException('Form not found');
                }
                $oldFormFields = $this->oldFieldsToArray($form->getFormFields());
                $form->removeFields();
                $form = $this->setFields($request, $form, $oldFormFields);

                $validator = $this->get('validator');
                $errors = $validator->validate($form);

                if (count($errors) > 0) {
                    $view = $this->view($errors, 500);
                } else {
                    $this->admin->update($form);
                    $view->setData(['id' => $form->getId(), 'message' => $this->get('translator')->trans('form_updated',
                        [], 'formGenerator')]);
                }
            }
        } catch (\Exception $e) {
            $view = $this->view(['message' => $e->getMessage()], 500);
        }

        $view->setFormat('json');

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param Form    $form
     *
     * @return Form
     */
    protected function setFields(Request $request, Form $form, $oldFormFieldsArray = array())
    {
        $form->setName($request->get('name'));
        $form->setInfoText($request->get('infoText'));
        $form->setThankYouText($request->get('thankYouText'));
        $form->setEmail($request->get('email'));
        $form->setAction($request->get('action'));
        $form->setRedirect($request->get('redirect'));

        $collection = $request->get('collection');

        foreach ($collection as $field) {
            if (is_array($field)) {
                switch ($field['title']) {
                    case 'Multiple Radios':
                    case 'Multiple Checkboxes':
                    case 'Multiple Checkboxes Inline':
                    case 'Multiple Radios Inline':
                        $formField = new FormField();
                        $name = $field['fields']['name']['value'];
                        $type = $field['title'];
                        $mapping = $this->findMapping($name, $type,  $oldFormFieldsArray);
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setName($name);
                        $formField->setType($type);
                        $formField->setOptions($field['fields']);
                        $formField->setMapping($mapping);
                        $form->addFormField($formField);
                        break;
                    case 'Legend':
                        $formField = new FormField();
                        $name = $field['fields']['id']['value'];
                        $type = $field['title'];
                        $mapping = $this->findMapping($name, $type, $oldFormFieldsArray);
                        $formField->setName($name);
                        $formField->setFieldLabel($field['fields']['name']['value']);
                        $formField->setType($type);
                        $formField->setOptions($field['fields']);
                        $formField->setMapping($mapping);
                        $form->addFormField($formField);
                        break;
                    default:
                        $formField = new FormField();
                        $name = $field['fields']['id']['value'];
                        $type = $field['title'];
                        $mapping = $this->findMapping($name, $type, $oldFormFieldsArray);
                        $formField->setName($name);
                        $formField->setFieldLabel($field['fields']['label']['value']);
                        $formField->setType($type);
                        $formField->setOptions($field['fields']);
                        $formField->setMapping($mapping);
                        $form->addFormField($formField);
                        break;
                }
            }
        }

        return $form;
    }


    /*
     * alte felder in array speicher
     * **/
    public function oldFieldsToArray($oldFormFields){
        $array = array();
        foreach($oldFormFields as $oldField){
            $array[] = array('name' => $oldField->getName(), 'type' => $oldField->getType(), 'mapping' => $oldField->getMapping());
        }
        $array[] = array('name' => 'phil', 'type' => 'type', 'mapping' => 'mapping');
        return $array;
    }



    /*
     * ermittelt, ob  zu diesem feld bereits ein mapping gespeichert wurde
     * */
    public function findMapping($name, $type, $oldFormFieldsArray){
        $mapping = '';
        foreach($oldFormFieldsArray as $oldField){

          if($oldField['name'] == $name and $oldField['type'] == $type){
              $mapping = $oldField['mapping'];
          }
        }
        //$mapping = print_r($oldFormFieldsArray, true);

        return $mapping;

    }


    /**
     * @Route(requirements={"_format"="json|xml"}, defaults={"_format": "json"})
     *
     * @param Request $request
     */
    public function deleteAction(Request $request, $id)
    {

        /** @var FormAdmin $admin */
        $admin = $this->get('Networking\FormGeneratorBundle\Admin\FormAdmin');

        $form = $admin->getObject($id);
        if (!$form) {
            throw new NotFoundHttpException('Form not found');
        }

        $admin->delete($form);
    }

    /*
     * deletes a single entry
     * */

    public function deleteFormEntryAction(Request $request, $id, $rowid)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NetworkingFormGeneratorBundle:FormData');

        $formData = $repo->find($rowid);
        $em->remove($formData);
        $em->flush();

        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    public function deleteAllFormEntryAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NetworkingFormGeneratorBundle:FormData');

        $formData = $repo->findBy(['form' => $id]);
        foreach ($formData as $record) {
            $em->remove($record);
            $em->flush();
        }

        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @return StreamedResponse
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function excelExportAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        /** @var Form $form */
        $form = $repo->find($id);
        $formFields = $form->getFormFields();
        $formData = $form->getFormData();
        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()->setCreator('initCms')
            ->setTitle('Export')
            ->setSubject('Export');

        $col = 'A';
        $row = '1';
        //Titel-Zeile ausgeben
        foreach ($formFields as $key => $field) {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, $field->getFieldLabel());
            ++$col;
        }
        $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, 'Date');

        //Daten ausgeben
        foreach ($formData as $rowData) {
            $col = 'A';
            ++$row;
            $formFields = $rowData->getFormFields();
            foreach ($formFields as $field) {
                $value = $field->getValue();
                if (is_array($value)) {
                    $value = implode(' ', $value);
                }
                $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, $value);
                ++$col;
            }
            $spreadsheet->setActiveSheetIndex(0)->setCellValue($col.$row, $rowData->getCreatedAt());
        }

        $spreadsheet->getActiveSheet()->setTitle('export');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // create the writer

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        // create the response
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            []
        );

        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'form-export-'.date('Y-m-d').'.xlsx'
        );

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }


    public function getAddressFieldArray()
    {
        $array = array();
        $array['Sprache'] = 'language';
        $array['Anrede'] = 'salutation';
        $array['Vorname'] = 'firstname';
        $array['Name'] = 'name';
        $array['Organisation'] = 'organisation';
        $array['Abteilung'] = 'departement';
        $array['Funktion'] = 'function';
        $array['Adresse 1'] = 'street1';
        $array['Adresse 2'] = 'street2';
        $array['PLZ'] = 'zip';
        $array['Ort'] = 'city';
        $array['Land'] = 'country';
        $array['Telefon'] = 'tel';
        $array['Mobile Telefon'] = 'mobile';
        $array['E-Mail'] = 'email';
        $array['URL'] = 'url';
        $array['Adresse 1 (privat)'] = 'street1Private';
        $array['Adresse 2 (privat)'] = 'street2Private';
        $array['PLZ (privat)'] = 'zipPrivate';
        $array['Ort (privat)'] = 'cityPrivate';
        $array['Land (privat)'] = 'countryPrivate';
        $array['Telefon (privat)'] = 'phonePrivate';
        $array['Mobile Telefon (privat)'] = 'mobilePrivate';
        $array['E-Mail (privat)'] = 'emailPrivate';
        $array['Korrespondenzadresse'] = 'corresponcende';
        $array['Kommentar'] = 'comment';
        $array['Geschlecht'] = 'sex';
        return $array;
    }


    /* ansicht form data =>

    Adresse verknüpfen ohne Daten zu übernehmen
    Formular Daten überschreiben die bestehenden Daten
    Folgende Formular Felder sollen übernommen werden*/


    /* verknuepft form data mit bestehender adresse */
    public function addMatchAction(Request $request, $id, $rowid, $addressid)
    {

        $param = array();
        $em = $this->getDoctrine()->getManager();
        $mappingArray = array('firstname' => '', 'name' => '', 'email' => '');
        $dataArray = $this->getDataArrayforMapping($request, $id, $rowid);

        //populate mapping array
        foreach ($dataArray as $row)
        {
           if(isset( $row['value']) and isset($row['mapping'])){
               $mappingArray[$row['mapping']] = $row['value'];
           }

        }

        $repoAddress = $this->getDoctrine()->getRepository(Address::class);
        $address = $repoAddress->find($addressid);

        $repoAddressCategory = $this->getDoctrine()->getRepository(AddressCategory::class);
        $addressCategoryList = $repoAddressCategory->findBy(array(), array('name' => 'asc'));

        if(isset($_POST['Submit']))
        {
            //formular wurde übermittelt, daten speichern
            $todo = $_POST['todo'];
            if($todo == 'option3')
            {   //Folgende Formular Felder sollen übernommen werden
                if(isset($_POST['language']))
                {   $address->setLanguage($_POST['language']);
                }

                if(isset($_POST['salutation']))
                {   $address->setSalutation($_POST['salutation']);
                }

                if(isset($_POST['firstname']))
                {   $address->setFirstname($_POST['firstname']);
                }

                if(isset($_POST['name']))
                {   $address->setName($_POST['name']);
                }

                if(isset($_POST['organisation']))
                {  $address->setOrganisation($_POST['organisation']);
                }

                if(isset($_POST['sex']))
                {  $address->setSex($_POST['sex']);
                }

                if(isset($_POST['departement']))
                {  $address->setDepartement($_POST['departement']);
                }

                if(isset($_POST['function']))
                {   $address->setFunction($_POST['function']);
                }

                if(isset($_POST['street1']))
                {   $address->setStreet1($_POST['street1']);
                }

                if(isset($_POST['street2']))
                {   $address->setStreet2($_POST['street2']);
                }

                if(isset($_POST['zip']))
                {   $address->setZip($_POST['zip']);
                }

                if(isset($_POST['city']))
                {    $address->setCity($_POST['city']);
                }

                if(isset($_POST['country']))
                {   $address->setCountry($_POST['country']);
                }

                if(isset($_POST['tel']))
                {   $address->setTel($_POST['tel']);
                }

                if(isset($_POST['mobile']))
                {   $address->setMobile($_POST['mobile']);
                }

                if(isset($_POST['email']))
                {   $address->setEmail($_POST['email']);
                }

                if(isset($_POST['url']))
                {   $address->setUrl($_POST['url']);
                }


                //daten speichern
                $em->persist($address);
                $em->flush();
            }
            elseif($todo == 'option2')
            {   //Formular Daten überschreiben die bestehenden Daten
                $address = $this->updateAddressDataValue($address, $dataArray);
            }


            //kategorien anpassen
            foreach ($addressCategoryList as $item)
            {
                if(isset($_POST['cat'][$item->getId()]) and $_POST['cat'][$item->getId()] != '' ){
                    //kategorie wurde ausgewaehlt
                    //echo "is set<br >";
                    if(!$address->getCategory()->contains($item)){
                        $address->addCategory($item);
                        //echo "add item<br />";
                    }
                }else{
                    //kategorie entfernen
                    if($address->getCategory()->contains($item)){
                        $address->removeCategory($item);
                        //echo "remove item<br />";
                    }
                }
            }
            $em->persist($address);
            $em->flush();

            $this->setAddressIdToFormData($rowid, $addressid);
            return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);

        }

        $param['dataArray'] = $dataArray;
        $param['id'] = $id;
        $param['rowid'] = $rowid;
        $param['mappingArray'] = $mappingArray;
        $param['address'] = $address;
        $param['addressCategoryList'] = $addressCategoryList;
        $param['addressArray'] = $this->transformAdressObjectToArray($address);


        return $this->renderWithExtraParams(
            '@NetworkingFormGenerator/Admin/addressShowMatchFields.html.twig',
            [
                'action' => 'mapping',
                'admin' => $this->admin,
                'dataArray' => $dataArray,
                'id' => $id,
                'rowid' => $rowid,
                'mappingArray' => $mappingArray,
                'address' => $address,
                'addressCategoryList' => $addressCategoryList,
                'addressArray' =>  $this->transformAdressObjectToArray($address)




            ]
        );

        //return $this->render('NetworkingFormGeneratorBundle:Admin:addressShowMatchFields.html.twig',$param);
    }


    public function setAddressIdToFormData($rowid, $addressid)
    {
        $em = $this->getDoctrine()->getManager();
        $repoData = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:FormData');
        $formData = $repoData->find($rowid);
        $formData->setAddressId($addressid);
        $em->persist($formData);
        $em->flush();
    }


    public function transformAdressObjectToArray(Address $address)
    {
        $array = array();
        $array['language'] = $address->getLanguage();
        $array['salutation'] = $address->getSalutation();
        $array['firstname'] = $address->getFirstname();
        $array['name'] = $address->getName();
        $array['organisation'] = $address->getOrganisation();
        $array['sex'] = $address->getSex();
        $array['departement'] = $address->getDepartement();
        $array['function'] = $address->getFunction();
        $array['street1'] = $address->getStreet1();
        $array['street2'] = $address->getStreet2();
        $array['zip'] = $address->getZip();
        $array['city'] = $address->getCity();
        $array['country'] = $address->getCountry();
        $array['tel'] = $address->getTel();
        $array['mobile'] = $address->getMobile();
        $array['email'] = $address->getEmail();
        $array['url'] = $address->getUrl();
        $array['street1Private'] = $address->getStreet1();
        $array['street2Private'] = $address->getStreet2();
        $array['zipPrivate'] = $address->getZipPrivate();
        $array['cityPrivate'] = $address->getCityPrivate();
        $array['countryPrivate'] = $address->getCountryPrivate();
        $array['phonePrivate'] = $address->getPhonePrivate();
        $array['mobilePrivate'] = $address->getMobilePrivate();
        $array['emailPrivate'] = $address->getEmailPrivate();
        $array['corresponcende'] = $address->getCorresponcende();
        $array['comment'] = $address->getComment();

        return $array;
    }

    public function updateAddressDataValue($address, $dataArray)
    {
        foreach ($dataArray as $row)
        {
            if ($row['value'] != '')
            {
                switch ($row['mapping'])
                {
                    case 'language':
                        $address->setLanguage($row['value']);
                        break;
                    case 'salutation':
                        $address->setSalutation($row['value']);
                        break;
                    case 'firstname':
                        $address->setFirstname($row['value']);
                        break;
                    case 'name':
                        $address->setName($row['value']);
                        break;
                    case 'organisation':
                        $address->setOrganisation($row['value']);
                        break;
                    case 'sex':
                        $address->setSex($row['value']);
                        break;
                    case 'departement':
                        $address->setDepartement($row['value']);
                        break;
                    case 'function':
                        $address->setFunction($row['value']);
                        break;
                    case 'street1':
                        $address->setStreet1($row['value']);
                        break;
                    case 'street2':
                        $address->setStreet2($row['value']);
                        break;
                    case 'zip':
                        $address->setZip($row['value']);
                        break;
                    case 'city':
                        $address->setCity($row['value']);
                        break;
                    case 'country':
                        $address->setCountry($row['value']);
                        break;
                    case 'tel':
                        $address->setTel($row['value']);
                        break;
                    case 'mobile':
                        $address->setMobile($row['value']);
                        break;
                    case 'email':
                        $address->setEmail($row['value']);
                        break;
                    case 'url':
                        $address->setUrl($row['value']);
                        break;


                    //TODO add more fields
                }
            }
        }
        //daten speichern
        $em = $this->getDoctrine()->getManager();
        $em->persist($address);
        $em->flush();
        return $address;
    }

    /*erstellt neue Adresse und gibt Address Object zurueck*/
    public function createNewAddress($request, $id, $rowid)
    {
        $dataArray = $this->getDataArrayforMapping($request, $id, $rowid);
        $em = $this->getDoctrine()->getManager();
        $address = new Address();
        $address->setStatus('aktiv');
        $address->setLanguage('de');
        $address->setCorresponcende('gesch');
        $address->setCreationDate(new \DateTime());
        $address = $this->updateAddressDataValue($address, $dataArray);
        $addressID = $address->getId();

        //address Id speichern
        $repoData = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:FormData');
        $formData = $repoData->find($rowid);
        $formData->setAddressId($addressID);
        $em->persist($formData);
        $em->flush();

        //rechte setzten
        //rechte anpassen
        $securityIdentity = new RoleSecurityIdentity("ROLE_ADMIN");

        $aclProvider = $this->get('security.acl.provider');
        $objectIdentity = ObjectIdentity::fromDomainObject($address);
        try {
            $acl = $aclProvider->findAcl($objectIdentity);
        } catch (\Symfony\Component\Security\Acl\Exception\AclNotFoundException $e) {
            $acl = $aclProvider->createAcl($objectIdentity);
        }

        $acl->insertClassAce($securityIdentity, MaskBuilder::MASK_OWNER);
        $aclProvider->updateAcl($acl);


        return $address;

    }


    /* erstellt neue adresse */
    public function addAddressAction(Request $request, $id, $rowid)
    {

        $this->createNewAddress($request, $id, $rowid);
        return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);

    }


    /*function that returns an array with  mapping an value, required for mapping and creating new user */
    public function getDataArrayforMapping(Request $request, $id, $rowid)
    {
        $dataArray = array();
        //get formular with all fields and matching fields
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        $form = $repo->find($id);
        $formFields = $form->getFormFields();
        foreach ($formFields as $formField) {
            $dataArray[$formField->getFieldLabel()] = array('id' => $formField->getId(), 'label' => $formField->getFieldLabel(), 'mapping' => $formField->getMapping());

        }
        //get formular data
        $repoData = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:FormData');
        $formData = $repoData->find($rowid);
        $formFieldData = $formData->getFormFields();
        foreach ($formFieldData as $formField) {
            $dataArray[$formField->getLabel()]['value'] = $formField->getValue();
        }

        return $dataArray;
    }


    /*
     * match overview, show all possible matches and possibility to create new address
     * */
    public function matchFormEntryAction(Request $request, $id, $rowid)
    {
        $param = array();
        $mappingArray = array('firstname' => '', 'name' => '', 'email' => '');
        $dataArray = $this->getDataArrayforMapping($request, $id, $rowid);

        //populate mapping array
        foreach ($dataArray as $row)
        {
            if(isset( $row['value']) and isset($row['mapping'])){
                $mappingArray[$row['mapping']] = $row['value'];
            }

        }

        //find possible matches
        $repoAddress = $this->getDoctrine()->getRepository(Address::class);
        $matches = $repoAddress->findMatches($mappingArray['firstname'], $mappingArray['name'], $mappingArray['email']);

        $formBuilder = $this->createFormBuilder(null, array('show_legend' => false));
        $formBuilder->add('addressCategory', EntityType::class, array(
            'class' => AddressCategory::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'label' => 'Kategorie'
        ));
        $formBuilder->add('send', SubmitType::class, array('label' => 'Neue Adresse erfassen'));
        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $data = $form->getData();
            $address = $this->createNewAddress($request, $id, $rowid);
            foreach($data['addressCategory'] as $addressCategory){
                $address->addCategory($addressCategory);
            }
            $em->persist($address);
            $em->flush();
            //umleitung auf formular uebersicht
            return $this->redirectToRoute('admin_networking_forms_show', ['id' => $id]);
        }

        $param['form'] = $form->createView();
        $param['dataArray'] = $dataArray;
        $param['id'] = $id;
        $param['rowid'] = $rowid;
        $param['mappingArray'] = $mappingArray;
        $param['matches'] = $matches;
        return $this->renderWithExtraParams(
            'NetworkingFormGeneratorBundle:Admin:addressMatch.html.twig',$param
        );
    }


    /*
     * form to match form fields to adress db
     * */
    public function addressConfigAction(Request $request, $id)
    {
        $param = array();
        $data = array();
        $showForm = true;
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        /** @var Form $form */
        $formEntity = $repo->find($id);
        $formFields = $formEntity->getFormFields();
        $addressFieldArray = $this->getAddressFieldArray();
        $message = 'Bitte mappen Sie die Felder vom Formular mit den Feldern aus der Adress-Datenbank.';

        $formBuilder = $this->createFormBuilder();
        foreach ($formFields as $formField) {
            $formBuilder->add($formField->getId(), ChoiceType::class, array(
                'choices' =>  $addressFieldArray,
                'label' => $formField->getFieldLabel(),
                'data' => $formField->getMapping(),
                'required' => false));
        }
        $formBuilder->add('send', SubmitType::class, array('label' => 'Speichern'));
        $form = $formBuilder->getForm();



        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            //mapping speichern
            foreach ($formFields as $formField)
            {
                //formular daten in mapping feld speichern
                if($data[$formField->getId()] != '')
                {   $formField->setMapping($data[$formField->getId()]);
                }else
                {   $formField->setMapping(NULL);
                }
                $em->persist($formField);
                $em->flush();
            }
            $message = 'Daten wurden gespeichert.';
            $showForm = false;
        }




        $param['action'] = 'address_config';
        $param['data'] = $data;
        $param['formObject'] = $formEntity;
        $param['form'] = $form->createView();
        $param['message'] = $message;
        $param['showForm'] = $showForm;

        return $this->renderWithExtraParams(
            'NetworkingFormGeneratorBundle:Admin:addressConfig.html.twig',$param
        );
    }


    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function statusAction(Request $request, $id)
    {

        //update status
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        $em = $this->getDoctrine()->getManager();
        /** @var Form $form */
        $form = $repo->find($id);
        if($form->getStatus() == 'online'){
            $form->setStatus('offline');
        }else{
            $form->setStatus('online');
        }
        $em->persist($form);
        $em->flush();

        return $this->redirect($this->admin->generateUrl('list'));
    }


    /**
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function copyAction(Request $request, $id)
    {
        $repo = $this->getDoctrine()->getRepository('NetworkingFormGeneratorBundle:Form');
        $em = $this->getDoctrine()->getManager();
        /** @var Form $form */
        $form = $repo->find($id);

        if (!$form) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ($request->getMethod() == 'POST') {
            try {
                /** @var Form $formCopy */
                $formCopy = clone $form;

                foreach ($form->getFormFields()->toArray() as $field) {
                    $fieldCopy = clone $field;
                    $formCopy->addFormField($fieldCopy);
                }

                $status = 'success';
                $message = $this->admin->trans(
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

            $this->get('session')->getFlashBag()->add(
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
     * Returns true if the request is a XMLHttpRequest.
     *
     * @return bool True if the request is an XMLHttpRequest, false otherwise
     */
    protected function isXmlHttpRequest()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        return $request->isXmlHttpRequest() || $request->get('_xml_http_request');
    }

    /**
     * Returns the base template name.
     *
     * @return string The template name
     */
    protected function getBaseTemplate()
    {
        if ($this->isXmlHttpRequest()) {
            return $this->admin->getTemplate('ajax');
        }

        return $this->admin->getTemplate('layout');
    }

	/**
	 * @param $view
	 * @param array $parameters
	 * @param Response|null $response
	 *
	 * @return Response
	 * @throws \Twig\Error\Error
	 * @throws \Twig_Error_Loader
	 * @throws \Twig_Error_Runtime
	 * @throws \Twig_Error_Syntax
	 */
    public function renderWithExtraParams($view, array $parameters = [], Response $response = null)
    {
        $parameters['admin'] = isset($parameters['admin']) ?
            $parameters['admin'] :
            $this->admin;

        $parameters['base_template'] = isset($parameters['base_template']) ?
            $parameters['base_template'] :
            $this->getBaseTemplate();

        $parameters['admin_pool'] = $this->get('sonata.admin.pool');

        return $this->renderTemplate($view, $parameters, $response);
    }

    /**
     * @param $view
     * @param array         $parameters
     * @param Response|null $response
     *
     * @return Response
     *
     * @throws \Twig\Error\Error
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderTemplate($view, array $parameters = [], Response $response = null)
    {
        if ($this->container->has('templating')) {
            $content = $this->container->get('templating')->render($view, $parameters);
        } elseif ($this->container->has('twig')) {
            $content = $this->container->get('twig')->render($view, $parameters);
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

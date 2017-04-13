<?php

namespace QuickbooksBundle\Controller\Samples;

use QuickbooksBundle\Service\QuickbooksEntityService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Data\IPPEstimate;
use QuickBooksOnline\API\Data\IPPItem;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPSalesItemLineDetail;
use QuickBooksOnline\API\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * Class InvoiceController
 * @package AppBundle\Controller
 * @Route("/estimates")
 */
class EstimateController extends Controller
{
    /**
     * @Route("/", name="estimates_create")
     */
    public function createAction()
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $estimate = new IPPEstimate();
        $estimate->DocNumber = rand(0,999);

        date_default_timezone_set('UTC');
        $estimate->TxnDate = date('Y-m-d', time());
        $estimate->ExpirationDate = date('Y-m-d', time()+ 15*(24*60*60));

        $line1 = new IPPLine();
        $line1->LineNum = "1";
        $line1->Amount = "300.00";

        $line1->DetailType = "SalesItemLineDetail";

        $salesItemLineDetail = new IPPSalesItemLineDetail();

        $item = new IPPItem();
        $item->Id = 3;

        $item = $qb_entity_manager->find($item);
        $salesItemLineDetail->ItemRef = $item->Id;

        $taxCodeRef = new IPPReferenceType();
        $taxCodeRef->Value = "NON";
        $salesItemLineDetail->TaxCodeRef = $taxCodeRef;
        $line1->SalesItemLineDetail = $salesItemLineDetail;
        $estimate->Line = array($line1);

//        $depositAccount = AccountHelper::getCashBankAccount($dataService);
//        $estimate->DepositToAccountRef = $depositAccount->Id;

        $customer = new IPPCustomer();
        $customer->Id = 58;

        $customer = $qb_entity_manager->find($customer);
        $estimate->CustomerRef = $customer->Id;

        $estimate->CustomerRef = $customer->Id;
        $estimate->ApplyTaxAfterDiscount = 'false';
        $estimate->TotalAmt = 300.00;
        $estimate->PrivateNote = "Accurate Estimate";

        $estimate = $qb_entity_manager->create($estimate);

        \Doctrine\Common\Util\Debug::dump($estimate);die;
    }

    /**
     * @Route("/{id}", name="estimates_find")
     * @param $id
     */
    public function findAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $query_estimate = new IPPEstimate();
        $query_estimate->Id = $id;

        $estimate = $qb_entity_manager->find($query_estimate);

        \Doctrine\Common\Util\Debug::dump($estimate);die;
    }

    /**
     * @Route("/query/", name="estimates_query")
     */
    public function queryAction()
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $estimates = $qb_entity_manager->query("SELECT * FROM Estimate");

        \Doctrine\Common\Util\Debug::dump($estimates);die;
    }

    /**
     * @Route("/update/{id}", name="estimates_update")
     */
    public function updateAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $estimate = new IPPEstimate();
        $estimate->Id = $id;

        $estimate_to_update = $qb_entity_manager->find($estimate);

        $estimate_to_update->PrivateNote = "JeanMichGwel";

        $estimate_updated = $qb_entity_manager->update($estimate_to_update);

        \Doctrine\Common\Util\Debug::dump($estimate_updated);die;
    }

    /**
     * @Route("/delete/{id}", name="estimates_delete")
     */
    public function deleteAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $estimate = new IPPEstimate();
        $estimate->Id = $id;

        $qb_entity_manager->delete($estimate);

        \Doctrine\Common\Util\Debug::dump("Dontthrow");die;
    }

    /**
     * @Route("/download/{id}", name="estimates_download")
     */
    public function downloadPdfAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $estimate = new IPPEstimate();
        $estimate->Id = $id;

        $path = $qb_entity_manager->downloadPdf($estimate);

        \Doctrine\Common\Util\Debug::dump("File written at ". $path);die;
    }

    /**
     * @Route("/send/{id}/{email}", name="estimates_send")
     */
    public function sendPdfAction($id, $email)
    {
        $emailConstraint = new EmailConstraint();
        $emailConstraint->message = 'Email address not valid.';

        $errors = $this->get('validator')->validate(
            $email,
            $emailConstraint
        );
        if ( count($errors) > 0)
            throw new ValidationException($emailConstraint->message, 400);

        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $estimate = new IPPEstimate();
        $estimate->Id = $id;

        $estimate = $qb_entity_manager->sendPdf($estimate, $email);

        \Doctrine\Common\Util\Debug::dump("Estimate sent.");die;
    }
}
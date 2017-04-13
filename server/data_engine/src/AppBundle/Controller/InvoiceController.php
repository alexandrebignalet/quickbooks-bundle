<?php

namespace AppBundle\Controller;

use QuickbooksBundle\Helper\Address;
use QuickbooksBundle\Service\QuickbooksEntityService;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Data\IPPInvoice;
use QuickBooksOnline\API\Data\IPPItem;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPLineDetailTypeEnum;
use QuickBooksOnline\API\Data\IPPPrintStatusEnum;
use QuickBooksOnline\API\Data\IPPSalesItemLineDetail;
use QuickBooksOnline\API\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;

/**
 * Class InvoiceController
 * @package AppBundle\Controller
 * @Route("/invoices")
 */
class InvoiceController extends Controller
{
    /**
     * @Route("/", name="invoices_create")
     */
    public function createAction()
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $invoice = new IPPInvoice();
        $invoice->DocNumber = rand(0,999);

        date_default_timezone_set('UTC');
        $invoice->TxnDate = date('Y-m-d', time());

        $customer = new IPPCustomer();
        $customer->Id = 58;

        $customer = $qb_entity_manager->find($customer);
        $invoice->CustomerRef = $customer->Id;

        // Optional Fields
        $invoice->PrivateNote = "Test Invoice";
        $invoice->TxnStatus = "Payable";
        $invoice->Balance = 10000;

        $invoice->BillAddr = Address::getPhysicalAddress();

        $line = new IPPLine();
        $line->Description = "test";
        $line->Amount = 10000;

        $linedetailTypeEnum = new IPPLineDetailTypeEnum();
        $line->DetailType = "SalesItemLineDetail";

        $silDetails = new IPPSalesItemLineDetail();

        $item = new IPPItem();
        $item->Id = 3;

        $item = $qb_entity_manager->find($item);
        $silDetails->ItemRef = $item->Id;

        $line->SalesItemLineDetail = $silDetails;

        $invoice->Line = array($line);

        $invoice->RemitToRef = $customer->Id;

        $printStatusEnum = new IPPPrintStatusEnum();
        $invoice->PrintStatus = "NeedToPrint";

        $invoice->TotalAmt = 10000;
        $invoice->FinanceCharge = 'false';

        $invoice = $qb_entity_manager->create($invoice);

        \Doctrine\Common\Util\Debug::dump($invoice);die;
    }

    /**
     * @Route("/{id}", name="invoices_find")
     * @param $id
     */
    public function findAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $query_invoice = new IPPInvoice();
        $query_invoice->Id = $id;

        $invoice = $qb_entity_manager->find($query_invoice);

        \Doctrine\Common\Util\Debug::dump($invoice);die;
    }

    /**
     * @Route("/query/", name="invoices_query")
     */
    public function queryAction()
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $invoices = $qb_entity_manager->query("SELECT * FROM Invoice");

        \Doctrine\Common\Util\Debug::dump($invoices);die;
    }

    /**
     * @Route("/update/{id}", name="invoices_update")
     */
    public function updateAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $invoice = new IPPInvoice();
        $invoice->Id = $id;

        $invoice_to_update = $qb_entity_manager->find($invoice);

        $invoice_to_update->PrivateNote = "JeanMichGwel";

        $invoice_updated = $qb_entity_manager->update($invoice_to_update);

        \Doctrine\Common\Util\Debug::dump($invoice_updated);die;
    }

    /**
     * @Route("/delete/{id}", name="invoices_delete")
     */
    public function deleteAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $invoice = new IPPInvoice();
        $invoice->Id = $id;

        $qb_entity_manager->delete($invoice);

        \Doctrine\Common\Util\Debug::dump("Dontthrow");die;
    }

    /**
     * @Route("/download/{id}", name="invoices_download")
     */
    public function downloadPdfAction($id)
    {
        /**
         * @var QuickbooksEntityService $qb_entity_manager
         */
        $qb_entity_manager = $this->get("qb.entity_manager");

        $invoice = new IPPInvoice();
        $invoice->Id = $id;

        $path = $qb_entity_manager->downloadPdf($invoice);

        \Doctrine\Common\Util\Debug::dump("File written at ". $path);die;
    }

    /**
     * @Route("/send/{id}/{email}", name="invoices_send")
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

        $invoice = new IPPInvoice();
        $invoice->Id = $id;

        $invoice = $qb_entity_manager->sendPdf($invoice, $email);

        \Doctrine\Common\Util\Debug::dump("Invoice sent.");die;
    }
}
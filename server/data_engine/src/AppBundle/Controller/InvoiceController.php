<?php

namespace AppBundle\Controller;

use QuickbooksBundle\Service\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class InvoiceController
 * @package AppBundle\Controller
 * @Route("/invoices")
 */
class InvoiceController extends Controller
{
    /**
     * @Route("/", name="invoice")
     */
    public function createAction()
    {
        /**
         * @var InvoiceService $invoice_service
         */
        $invoice_service = $this->get("qb.invoices");

        $invoice = $invoice_service->createInvoice();

        return new Response($invoice);
    }
}
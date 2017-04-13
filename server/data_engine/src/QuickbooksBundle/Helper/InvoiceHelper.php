<?php

namespace QuickbooksBundle\Helper;


use QuickBooksOnline\API\DataService\IPPIntuitEntity;
use QuickBooksOnline\API\Data\IPPInvoice;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPLineDetailTypeEnum;
use QuickBooksOnline\API\Data\IPPPrintStatusEnum;
use QuickBooksOnline\API\Data\IPPSalesItemLineDetail;
use QuickBooksOnline\API\DataService\DataService;

class InvoiceHelper {

    /**
     * @param DataService $dataService
     * @return IPPIntuitEntity
     */
    static function getInvoiceFields(DataService $dataService) {
        $invoice = new IPPInvoice();
        $invoice->DocNumber = rand(0,999);

        date_default_timezone_set('UTC');
        $invoice->TxnDate = date('Y-m-d', time());

        $customer = CustomerHelper::getCustomer($dataService);
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
        $line->DetailType = IPPLineDetailTypeEnum::IPPLINEDETAILTYPEENUM_SALESITEMLINEDETAIL;

        $silDetails = new IPPSalesItemLineDetail();

        $item = ItemHelper::getItem($dataService);
        $silDetails->ItemRef = $item->Id;

        $line->SalesItemLineDetail = $silDetails;

        $invoice->Line = array($line);

        $invoice->RemitToRef = $customer->Id;

        $printStatusEnum = new IPPPrintStatusEnum();
        $invoice->PrintStatus = $printStatusEnum::IPPPRINTSTATUSENUM_NEEDTOPRINT;

        $invoice->TotalAmt = 10000;
        $invoice->FinanceCharge = 'false';
        return $invoice;
    }

    static function createInvoice(DataService $dataService) {
        return $dataService->Add(InvoiceHelper::getInvoiceFields($dataService));
    }

    static function getInvoice(DataService $dataService) {
        $allInvoices = $dataService->FindAll('Invoice', 0, 500);
        if (!$allInvoices || (0==count($allInvoices))) {
            return InvoiceHelper::createInvoice($dataService);
        } else {
            return $allInvoices[0];
        }
    }

}

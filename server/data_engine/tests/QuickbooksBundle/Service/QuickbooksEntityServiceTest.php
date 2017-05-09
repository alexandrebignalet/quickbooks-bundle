<?php

namespace Tests\QuickbooksBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use QuickbooksBundle\Entity\OAuthInfo;
use QuickbooksBundle\Repository\OAuthInfoRepository;
use QuickbooksBundle\Service\QuickbooksEntityService;
use PHPUnit\Framework\TestCase;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\Data\IPPCustomer;
use QuickBooksOnline\API\Data\IPPIntuitEntity;
use QuickBooksOnline\API\Data\IPPInvoice;
use QuickBooksOnline\API\Data\IPPItem;
use QuickBooksOnline\API\Data\IPPLine;
use QuickBooksOnline\API\Data\IPPPhysicalAddress;
use QuickBooksOnline\API\Data\IPPSalesItemLineDetail;
use QuickBooksOnline\API\DataService\DataService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class QuickbooksEntityServiceTest
 * @package Tests\QuickbooksBundle\Service
 *
 * You must connect your App to Quickbooks before launching this test
 * Fill in your parameters.yml and visit /oauth_connection
 */
class QuickbooksEntityServiceTest extends KernelTestCase
{
    /**
     * @var ContainerInterface $container
     */
    private static $container;

    /**
     * @var QuickbooksEntityService $qb_entity_service
     */
    private static $qb_entity_service;

    /**
     * @var OAuthInfoRepository $oauth_info_repository
     */
    private static $oauth_info_repository;

    public static function setUpBeforeClass()
    {
        /**
         * Start the symfony kernel
         *
         * @var Kernel $kernel
         */
        self::bootKernel();

        //get the DI container
        self::$container = static::$kernel->getContainer();

        //now we can instantiate our service (if you want a fresh one for
        //each test method, do this in setUp() instead
        self::$qb_entity_service = self::$container->get('qb.entity_manager');
        self::$oauth_info_repository = self::$container->get('qb.repository.oauth_info');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$qb_entity_service = null;
        self::$oauth_info_repository = null;
        self::$container = null;

        self::ensureKernelShutdown();
    }

    public function testConstruct()
    {
        $base_url = self::$container->getParameter('quickbooks.base_url');
        $export_dir = self::$container->getParameter('quickbooks.export_dir');
        $consumer_key = self::$container->getParameter('quickbooks.oauth.consumer_key');
        $consumer_secret = self::$container->getParameter('quickbooks.oauth.consumer_secret');

        $quickbooks_entity_service = new QuickbooksEntityService(
            self::$oauth_info_repository,
            $base_url,
            $export_dir,
            $consumer_key,
            $consumer_secret
        );

        $this->assertAttributeInstanceOf(OAuthInfo::class, "oauth_info", $quickbooks_entity_service);
        $this->assertAttributeEquals($base_url, "base_url", $quickbooks_entity_service);
        $this->assertAttributeEquals($export_dir, "export_dir", $quickbooks_entity_service);
        $this->assertAttributeEquals($consumer_key, "consumer_key", $quickbooks_entity_service);
        $this->assertAttributeEquals($consumer_secret, "consumer_secret", $quickbooks_entity_service);
        $this->assertAttributeInstanceOf(DataService::class, "data_service", $quickbooks_entity_service);
        $this->assertAttributeInstanceOf(ServiceContext::class, "service_context", $quickbooks_entity_service);
    }

    public function testCreate()
    {
        $ipp_invoice = $this->createInvoice();
        $invoice_created = self::$qb_entity_service->create($ipp_invoice);

        $this->assertInstanceOf(IPPIntuitEntity::class, $invoice_created);
        $this->assertLessThan(1000, $invoice_created->DocNumber);

        $this->assertEquals($invoice_created->DocNumber, $ipp_invoice->DocNumber);
    }

    public function testQuery()
    {
        $customers = self::$qb_entity_service->query("SELECT * FROM Customer");

        $this->assertNotEmpty($customers);
    }

    public function testUpdate()
    {
        $invoice_retrieved = self::$qb_entity_service->query("SELECT * FROM Invoice")[0];
        $invoice_retrieved->PrivateNote = "Je suis mis à jour";

        $invoice_updated = self::$qb_entity_service->update($invoice_retrieved);

        $this->assertInstanceOf(IPPIntuitEntity::class, $invoice_updated);
        $this->assertEquals($invoice_retrieved->PrivateNote, $invoice_updated->PrivateNote);
    }

    public function testDelete()
    {
        $invoice_to_delete = self::$qb_entity_service->query("SELECT * FROM Invoice")[0];

        self::$qb_entity_service->delete($invoice_to_delete);

        $result = self::$qb_entity_service->find($invoice_to_delete);

        $this->assertEquals($result, null);
    }

    private function createInvoice()
    {
        $invoice = new IPPInvoice();
        $invoice->DocNumber = rand(0,999);

        date_default_timezone_set('UTC');
        $invoice->TxnDate = date('Y-m-d', time());

        $customer = self::$qb_entity_service->query("SELECT * FROM Customer")[0];

        $invoice->CustomerRef = $customer->Id;

        // Optional Fields
        $invoice->PrivateNote = "Test Invoice";
        $invoice->TxnStatus = "Payable";
        $invoice->Balance = 10000;

        $address = new IPPPhysicalAddress();
        $address->Line1 = "123 Main St";
        $address->City = "Mountain View";
        $address->Country = "United States";
        $address->CountrySubDivisionCode = "CA";
        $address->PostalCode  = "94043";

        $invoice->BillAddr = $address;

        $line = new IPPLine();
        $line->Description = "test";
        $line->Amount = 10000;

        $line->DetailType = "SalesItemLineDetail";

        $silDetails = new IPPSalesItemLineDetail();

        $item = self::$qb_entity_service->query("SELECT * FROM Item")[0];

        $silDetails->ItemRef = $item->Id;

        $line->SalesItemLineDetail = $silDetails;

        $invoice->Line = array($line);

        $invoice->RemitToRef = $customer->Id;

        $invoice->PrintStatus = "NeedToPrint";

        $invoice->TotalAmt = 10000;
        $invoice->FinanceCharge = 'false';

        return $invoice;
    }
}
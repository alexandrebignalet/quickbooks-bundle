<?php

namespace QuickbooksBundle\Service;

use QuickbooksBundle\Entity\OAuthInfo;
use QuickbooksBundle\Repository\OAuthInfoRepository;
use QuickBooksOnline\API\Core\CoreConstants;
use QuickBooksOnline\API\Core\ServiceContext;
use QuickBooksOnline\API\Data\IPPNameBase;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Security\OAuthRequestValidator;
use SensioLabs\Security\Exception\RuntimeException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class QuickbooksEntityService
 * @package QuickbooksBundle\Service
 *
 * Base class used to initialize API communication services
 * Use Dependency Injection in order to obtain constructors parameters from parameters.yml
 */
class QuickbooksEntityService
{
    /**
     * @var OAuthInfo $oauth_info
     */
    private $oauth_info;

    /**
     * @var string $base_url
     */
    private $base_url;

    /**
     * @var string $export_dir
     */
    private $export_dir;

    /**
     * @var string $consumer_key
     */
    private $consumer_key;

    /**
     * @var string $consumer_secret
     */
    private $consumer_secret;

    /**
     * @var DataService $data_service
     */
    protected $data_service;

    /**
     * InvoiceService constructor.
     *
     * @param OAuthInfoRepository $repository
     * @param string $consumer_key
     * @param string $consumer_secret
     */
    public function __construct($repository, $base_url, $export_dir, $consumer_key, $consumer_secret)
    {
        $this->base_url = $base_url;
        $this->export_dir = $export_dir;
        $this->oauth_info = $repository->get();

        if ($this->oauth_info === null)
            throw new AuthenticationException("Not authenticated to quickbooks");

        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;

        $request_validator = new OAuthRequestValidator(
            $this->oauth_info->getAccessToken(),
            $this->oauth_info->getAccessTokenSecret(),
            $this->consumer_key,
            $this->consumer_secret
        );

        $service_context = new ServiceContext($this->oauth_info->getCompanyId(), CoreConstants::IntuitServicesTypeQBO, $request_validator);
        if (!$service_context)
            throw new AuthenticationException("Problem while initializing ServiceContext.");

        $service_context->IppConfiguration->BaseUrl->Qbo = $this->base_url;
        $service_context->IppConfiguration->ContentWriter->strategy = CoreConstants::EXPORT_STRATEGY;
        $service_context->IppConfiguration->ContentWriter->exportDir = $this->export_dir;

        $this->data_service = new DataService($service_context);
        if (!$this->data_service)
            throw new AuthenticationException("Problem while initializing DataService.");
    }

    /**
     * @param $ipp_entity
     * @return \QuickBooksOnline\API\DataService\IPPIntuitEntity
     */
    public function create($ipp_entity)
    {
        $object = $this->data_service->Add($ipp_entity);

        $error = $this->data_service->getLastError();

        if ($error != null)
            throw new RuntimeException($error->getResponseBody(), $error->getHttpStatusCode());

        return $object;
    }

    /**
     * @param string
     * @return array
     */
    public function query($sql_query)
    {
        /**
         * @var array $entities
         */
        $objects = $this->data_service->Query($sql_query);

        $error = $this->data_service->getLastError();

        if ($error != null)
            throw new RuntimeException($error->getResponseBody(), $error->getHttpStatusCode());

        return $objects;

    }

    /**
     * @param $ipp_entity
     * @return \QuickBooksOnline\API\DataService\IPPIntuitEntity
     */
    public function find($ipp_entity)
    {
        return $this->data_service->findById($ipp_entity);
    }

    /**
     * @param string
     * @return \QuickBooksOnline\API\DataService\IPPIntuitEntity
     */
    public function update($ipp_entity)
    {
        $ipp_entity->sparse = true;
        $updated_entity = $this->data_service->Update($ipp_entity);
        $error = $this->data_service->getLastError();

        if ($error != null)
            throw new RuntimeException($error->getResponseBody(), $error->getHttpStatusCode());
        return $updated_entity;
    }

    /**
     * @param $ipp_entity
     * @return bool
     */
    public function delete($ipp_entity)
    {
        $object_to_delete = $this->find($ipp_entity);

        $this->data_service->Delete($object_to_delete);

        $error = $this->data_service->getLastError();

        if ($error != null)
            throw new RuntimeException($error->getResponseBody(), $error->getHttpStatusCode());

        return true;
    }

    /**
     * @param $ipp_entity
     *
     * @return string $path
     */
    public function downloadPdf($ipp_entity)
    {
        $object_to_download = $this->find($ipp_entity);

        $path = $this->data_service->DownloadPDF($object_to_download);

        $error = $this->data_service->getLastError();

        if ($error != null)
            throw new RuntimeException($error->getResponseBody(), $error->getHttpStatusCode());

        return $path;
    }

    /**
     * @param $ipp_entity
     *
     * @return string $path
     */
    public function sendPdf($ipp_entity, $email)
    {
        $object_to_send = $this->find($ipp_entity);

        $path = $this->data_service->SendEmail($object_to_send, $email);

        $error = $this->data_service->getLastError();

        if ($error != null)
            throw new RuntimeException($error->getResponseBody(), $error->getHttpStatusCode());

        return $path;
    }
}
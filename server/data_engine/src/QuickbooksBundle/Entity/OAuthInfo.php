<?php

namespace QuickbooksBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 *
 * Unique object stored in database in order to keep the Quickbooks access tokens
 * Give the token validity according to Quickbooks Doc
 *
 * @ORM\Entity(repositoryClass="QuickbooksBundle\Repository\OAuthInfoRepository")
 * @ORM\Table(name="oauth_info")
 */
class OAuthInfo
{
    /**
     * Quickbooks advices to regenerate the token between 150 and 180 days after generation
     * No endpoints can give us the validity of this tokens, this way we can regenerate it automatically
     */
    const TOKEN_VALIDITY_DURATION = 150;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $access_token;

    /**
     * @ORM\Column(type="string")
     */
    private $access_token_secret;

    /**
     * @ORM\Column(type="string")
     */
    private $company_id;

    /**
     * @ORM\Column(type="string")
     */
    private $data_source;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $token_generation_date;

    /**
     * OAuthInfo constructor.
     * @param $access_token
     * @param $access_token_secret
     * @param $company_id
     * @param $data_source
     */
    public function __construct($access_token, $access_token_secret, $company_id, $data_source)
    {
        $this->access_token = $access_token;
        $this->access_token_secret = $access_token_secret;
        $this->company_id = $company_id;
        $this->data_source = $data_source;
        $this->token_generation_date = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getAccessTokenSecret()
    {
        return $this->access_token_secret;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @return string
     */
    public function getCompanyId()
    {
        return $this->company_id;
    }

    /**
     * @return bool
     */
    public function isTokenValid()
    {
        $today_date = new \DateTime('now');

        /**
         * @var \DateInterval
         */
        $date_interval = $this->token_generation_date->diff($today_date);

        return $date_interval->days < OAuthInfo::TOKEN_VALIDITY_DURATION;
    }

    /**
     * @return string
     */
    public function getDataSource()
    {
        return $this->data_source;
    }

    /**
     * @return bool
     */
    public function isAuthenticated() {
        return $this->access_token !== null && $this->access_token_secret !== null && $this->isTokenValid();
    }
}
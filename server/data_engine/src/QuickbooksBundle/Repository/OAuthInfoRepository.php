<?php

namespace QuickbooksBundle\Repository;

use QuickbooksBundle\Entity\OAuthInfo;
use Doctrine\ORM\EntityRepository;

class OAuthInfoRepository extends EntityRepository
{
    /**
     * @return null|OAuthInfo
     */
    public function get()
    {
        $data = $this->findBy([], null, 1);

        if (count($data) < 1) {
            return null;
        }

        return $data[0];
    }
}
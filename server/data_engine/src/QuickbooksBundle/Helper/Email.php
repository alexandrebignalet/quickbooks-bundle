<?php

namespace QuickbooksBundle\Helper;

use QuickBooksOnline\API\Data\IPPEmailAddress;

class Email {
    static function getEmailAddress() {
        $emailAddr = new IPPEmailAddress();
        $emailAddr->Address = "test@abc.com";
        return $emailAddr;
    }

}

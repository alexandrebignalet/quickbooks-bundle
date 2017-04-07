<?php

namespace QuickbooksBundle;

use QuickbooksBundle\DependencyInjection\QuickbooksExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class QuickbooksBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new QuickbooksExtension();
    }
}

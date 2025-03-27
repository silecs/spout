<?php

namespace Box\Spout\Reader\ODS\Creator;

use Box\Spout\Reader\Common\Manager\RowManager;

/**
 * Class ManagerFactory
 * Factory to create managers
 */
class ManagerFactory
{
    public function createRowManager(InternalEntityFactory$entityFactory): RowManager
    {
        return new RowManager($entityFactory);
    }
}

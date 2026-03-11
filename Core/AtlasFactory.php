<?php

namespace Core;

use Atlas\Orm\Atlas;
use Atlas\Orm\AtlasContainer;

class AtlasFactory
{
    public static function create(): Atlas
    {
        $config = require __DIR__ . '/../config.php';
        $container = new AtlasContainer(
            $config['db']['dsn'],
            $config['db']['username'],
            $config['db']['password']
        );

        $container->setMapperClasses([
            \Core\DataSource\AccountType\AccountTypeMapper::CLASS,
            // others...
        ]);

        return $container->getAtlas();
    }
}

$records = $atlas->select(\Core\DataSource\AccountType\AccountTypeMapper::class)->fetchRecordSet();
var_dump($records);

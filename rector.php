<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::DEAD_CODE);
    $containerConfigurator->import(SetList::PHP_81);
    $containerConfigurator->import(SymfonySetList::SYMFONY_52);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(SymfonySetList::SYMFONY_52_VALIDATOR_ATTRIBUTES);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION);
    $containerConfigurator->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [__DIR__]);
    $parameters->set(Option::SKIP, [__DIR__ . '/.github', __DIR__ . '/vendor']);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_81);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, true);

    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};

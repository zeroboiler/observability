<?php
/**
 * This file is part of ZeroBoiler, licensed under the proprietary license.
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    // PHP upgrades
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_85,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
    ]);

    // PHPUnit
    $rectorConfig->sets([
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ]);

    // Auto-import
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    // Parallel
    $rectorConfig->parallel();
};

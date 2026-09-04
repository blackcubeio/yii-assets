<?php

declare(strict_types=1);

/**
 * configuration.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Yiisoft\Config\Modifier\RecursiveMerge;

return [
    'config-plugin' => [
        'params-console' => [
            'console/params.php',
        ],
    ],
    'config-plugin-options' => [
        'source-directory' => 'config',
    ],
];
<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_timestamp
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

error_reporting(E_ALL);

function includeIfExists($file)
{
    return file_exists($file) ? include $file : false;
}

if (
    // Locally installed dependencies
    (!$loader = includeIfExists(__DIR__.'/../vendor/autoload.php'))
    // We are within an composer install.
    && (!$loader = includeIfExists(__DIR__.'/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

/**
 * Return an unserialized array or the argument
 * @param mixed
 * @param boolean
 * @return mixed
 */
function deserialize($varValue, $blnForceArray=false)
{
    // Already an array
    if (is_array($varValue)) {
        return $varValue;
    }

    // Null
    if ($varValue === null) {
        return $blnForceArray ? array() : null;
    }

    // Not a string
    if (!is_string($varValue)) {
        return $blnForceArray ? array($varValue) : $varValue;
    }

    // Empty string
    if (trim($varValue) == '') {
        return $blnForceArray ? array() : '';
    }

    $varUnserialized = @unserialize($varValue);

    if (is_array($varUnserialized)) {
        $varValue = $varUnserialized;
    } elseif ($blnForceArray) {
        $varValue = array($varValue);
    }

    return $varValue;
}

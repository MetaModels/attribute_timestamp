<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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

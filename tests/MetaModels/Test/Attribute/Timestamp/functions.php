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

/**
 * Return an unserialized array or the argument.
 *
 * @param mixed $varValue      The value to decode.
 * @param bool  $blnForceArray Flag if an array should be enforced.
 *
 * @return mixed
 */
function deserialize($varValue, $blnForceArray = false)
{
    // Already an array
    if (is_array($varValue)) {
        return $varValue;
    }

    if (null === $varValue) {
        return $blnForceArray ? array() : null;
    }

    // Not a string
    if (!is_string($varValue)) {
        return $blnForceArray ? array($varValue) : $varValue;
    }

    // Empty string
    if ('' === trim($varValue)) {
        return $blnForceArray ? array() : '';
    }

    // @codingStandardsIgnoreStart
    $varUnserialized = @unserialize($varValue);
    // @codingStandardsIgnoreEnd

    if (is_array($varUnserialized)) {
        $varValue = $varUnserialized;
    } elseif ($blnForceArray) {
        $varValue = array($varValue);
    }

    return $varValue;
}

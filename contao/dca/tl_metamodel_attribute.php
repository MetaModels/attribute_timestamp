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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */
$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['timestamp extends _simpleattribute_'] = [
    'timesettings' => ['timetype'],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['timetype'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['timetype'],
    'exclude'   => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['timetypeOptions'],
    'options'   => [
        'time',
        'date',
        'datim',
    ],
    'sql'       => 'varchar(64) NOT NULL default \'\'',
    'eval'      => [
        'doNotSaveEmpty' => true,
        'tl_class'       => 'w50',
    ],
];

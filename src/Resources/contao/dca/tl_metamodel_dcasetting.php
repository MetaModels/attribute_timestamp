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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id']['timestamp'] = [
    'presentation' => [
        'tl_class',
    ],
    'functions'    => [
        'mandatory',
        'clear_datetime',
    ],
    'overview'     => [
        'filterable',
        'searchable',
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields']['clear_datetime'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['clear_datetime'],
    'exclude'   => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['clear_datetime_options'],
    'options'   => [
        'time',
        'date',
    ],
    'sql'       => 'varchar(64) NOT NULL default \'\'',
    'eval'      => [
        'tl_class'       => 'w50',
        'includeBlankOption' => true
    ],
];

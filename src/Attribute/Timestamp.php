<?php

/**
 * This file is part of MetaModels/attribute_timestamp.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeTimestamp
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Henry Lamorski <henry.lamorski@mailbox.org>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\Attribute;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use MetaModels\AttributeNumericBundle\Attribute\Numeric;
use MetaModels\Render\Setting\ISimple;
use MetaModels\Render\Template;

/**
 * This is the MetaModelAttribute class for handling text fields.
 */
class Timestamp extends Numeric
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'bigint(10) NULL default NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $strDateType                 = $this->getDateTimeType();
        $arrFieldDef                 = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['eval']['rgxp'] = $strDateType;
        
        if (empty($arrFieldDef['eval']['readonly'])) {
            $arrFieldDef['eval']['datepicker'] = true;
            $arrFieldDef['eval']['tl_class']  .= ' wizard';
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(parent::getAttributeSettingNames(), array(
            'timetype',
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings)
    {
        $dispatcher = $this->getMetaModel()->getServiceContainer()->getEventDispatcher();

        parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);

        /** @var ISimple $objSettings */
        if ($objSettings->get('timeformat')) {
            $objTemplate->format = $objSettings->get('timeformat');
        } else {
            $objTemplate->format = $this->getDateTimeFormatString();
        }

        if (!empty($objTemplate->raw)) {
            /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
            $event = new ParseDateEvent($objTemplate->raw, $objTemplate->format);
            $dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);
            $objTemplate->parsedDate = $event->getResult();
        } else {
            $objTemplate->parsedDate = null;
        }
    }

    /**
     * Retrieve the selected type or fallback to 'date' if none selected.
     *
     * @return string
     */
    public function getDateTimeType()
    {
        return $this->get('timetype') ?: 'date';
    }

    /**
     * Retrieve the selected type or fallback to 'date' if none selected.
     *
     * @return string
     */
    public function getDateTimeFormatString()
    {
        $format = $this->getDateTimeType() . 'Format';
        $page   = $this->getObjPage();
        if ($page && $page->$format) {
            return $page->$format;
        }

        return \Config::get($format);
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $itemId)
    {
        // Check if we have some data.
        if ($varValue === '') {
            return null;
        }

        // If numeric we have already a integer value.
        if (is_numeric($varValue)) {
            return intval($varValue);
        }

        // @deprecated Parsing of string times is deprecated. Use the EncodePropertyValueFromWidgetEvent of the DCG
        // instead.
        // Make a unix timestamp from the string.
        $date = new \DateTime($varValue);
        return $date->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $dispatcher = $this->getMetaModel()->getServiceContainer()->getEventDispatcher();
        $format     = $this->getDateTimeFormatString();
        return array_map(
            function ($value) use ($format, $dispatcher) {
                $event = new ParseDateEvent($value, $format);
                $dispatcher->dispatch(ContaoEvents::DATE_PARSE, $event);

                return $event->getResult();
            },
            parent::getFilterOptions($idList, $usedOnly, $arrCount)
        );
    }

    /**
     * Returns the global page object (replacement for super globals access).
     *
     * @return mixed The global page object
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getObjPage()
    {
        return isset($GLOBALS['objPage']) ? $GLOBALS['objPage'] : null;
    }
}

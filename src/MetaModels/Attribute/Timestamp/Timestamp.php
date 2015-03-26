<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage AttributeTimestamp
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\Timestamp;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use MetaModels\Attribute\Numeric\Numeric;
use MetaModels\Render\Setting\ISimple;
use MetaModels\Render\Template;

/**
 * This is the MetaModelAttribute class for handling text fields.
 *
 * @package    MetaModels
 * @subpackage AttributeTimestamp
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
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
        $strDateType                       = $this->getDateTimeType();
        $arrFieldDef                       = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['eval']['rgxp']       = $strDateType;
        $arrFieldDef['eval']['datepicker'] = ($strDateType !== 'time');

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
    protected function getDateTimeType()
    {
        return $this->get('timetype') ?: 'date';
    }

    /**
     * Retrieve the selected type or fallback to 'date' if none selected.
     *
     * @return string
     */
    protected function getDateTimeFormatString()
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
     *
     * @throws \RuntimeException When an invalid time format name is encountered.
     */
    public function valueToWidget($varValue)
    {
        if ($varValue === null) {
            return '';
        }

        // We need to parse the 0 timestamp manually because the widget will display an empty string.
        if ($varValue === 0 || $varValue === '') {
            return '';
        }

        $format = \Config::get($this->getDateTimeType() . 'Format');
        if (empty($format)) {
            throw new \RuntimeException('Invalid time format name: ' . $this->getDateTimeType());
        }

        return date($format, $varValue);
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

        // Make a unix timestamp from the string.
        $date = new \DateTime($varValue);

        return $date->getTimestamp();
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

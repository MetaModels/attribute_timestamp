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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Isaak <info@andreas-isaak.de>
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
 * @package	   MetaModels
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
        $strDateType                       = $this->get('timetype');
        $arrFieldDef                       = parent::getFieldDefinition($arrOverrides);
        $arrFieldDef['eval']['rgxp']       = empty($strDateType) ? 'date' : $strDateType;
        $arrFieldDef['eval']['datepicker'] = ($strDateType == 'time') ? false : true;

        return $arrFieldDef;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(parent::getAttributeSettingNames(), array(
            'timetype'
        ));
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings = null)
    {
        $objPage    = $this->getObjPage();
        $arrConfig  = $this->getConfigArray();
        $dispatcher = $this->getEventDispatcher();

        parent::prepareTemplate($objTemplate, $arrRowData, $objSettings);

        /** @var ISimple $objSettings */
        if ($objSettings->get('timeformat')) {
            $objTemplate->format = $objSettings->get('timeformat');
        } else {
            $strDateType   = $this->get('timetype');
            $strFormatName = (empty($strDateType) ? 'date' : $strDateType) . 'Format';
            if ($objPage && $objPage->$strFormatName) {
                $objTemplate->format = $objPage->$strFormatName;
            } else {
                $objTemplate->format = $arrConfig[$strFormatName];
            }
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
     * {@inheritdoc}
     */
    public function valueToWidget($varValue)
    {
        $arrConfig = $this->getConfigArray();

        if ($varValue === null) {
            return '';
        }

        if ($varValue != 0) {
            return $varValue;
        }

        // We need to parse the 0 timestamp manually because the widget will display an empty string.
        if ($varValue === 0 || $varValue === '') {
            return '';
        }

        // Get the right format for the field.
        switch ($this->get('timetype')) {
            case 'time':
                $strDateType = $arrConfig['timeFormat'];
                break;

            case 'date':
                $strDateType = $arrConfig['dateFormat'];
                break;

            case 'datim':
                $strDateType = $arrConfig['datimFormat'];
                break;

            default:
                return $varValue;
        }

        // Return the data.
        return date($strDateType, $varValue);
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $intId)
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
    public function getObjPage()
    {
        return $GLOBALS['objPage'];
    }

    /**
     * Returns the global TL_CONFIG array (replacement for super globals access).
     *
     * @return mixed The global config array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getConfigArray()
    {
        return $GLOBALS['TL_CONFIG'];
    }

    /**
     * Returns the event dispatcher (replacement for super globals access).
     *
     * @return mixed The event dispatcher
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getEventDispatcher()
    {
        return $GLOBALS['container']['event-dispatcher'];
    }
}

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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\Timestamp;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\Events\BaseSubscriber;

/**
 * Handles event operations for timestamp attributes.
 *
 * @package MetaModels\Attribute\Timestamp
 */
class BackendSubscriber extends BaseSubscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'handleEncodePropertyValueFromWidget')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'handleDecodePropertyValueForWidgetEvent')
            );
    }

    /**
     * Encode an timestamp attribute value from a widget value.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The subscribed event.
     *
     * @return void
     */
    public function handleEncodePropertyValueFromWidget(EncodePropertyValueFromWidgetEvent $event)
    {
        $attribute = $this->getSupportedAttribute($event);
        if (!$attribute) {
            return;
        }

        $date = \DateTime::createFromFormat($attribute->getDateTimeFormatString(), $event->getValue());

        if ($date) {
            $event->setValue($date->getTimestamp());
        }
    }

    /**
     * Decode an timestamp attribute value for a widget value.
     *
     * @param DecodePropertyValueForWidgetEvent $event The subscribed event.
     *
     * @return void
     */
    public function handleDecodePropertyValueForWidgetEvent(DecodePropertyValueForWidgetEvent $event)
    {
        $attribute = $this->getSupportedAttribute($event);
        if (!$attribute) {
            return;
        }

        $dispatcher = $event->getEnvironment()->getEventDispatcher();
        $value      = $event->getValue();

        if (is_numeric($value)) {
            $dateEvent = new ParseDateEvent($value, $attribute->getDateTimeFormatString());
            $dispatcher->dispatch(ContaoEvents::DATE_PARSE, $dateEvent);

            $event->setValue($dateEvent->getResult());
        }
    }

    /**
     * Get the supported attribute or null.
     *
     * @param EncodePropertyValueFromWidgetEvent|DecodePropertyValueForWidgetEvent $event The subscribed event.
     *
     * @return Timestamp|null
     */
    private function getSupportedAttribute($event)
    {
        $model = $event->getModel();

        // Not a metamodel model.
        if (!$model instanceof Model) {
            return null;
        }

        $property  = $event->getProperty();
        $attribute = $model->getItem()->getAttribute($property);

        if ($attribute instanceof Timestamp) {
            return $attribute;
        }

        return null;
    }
}

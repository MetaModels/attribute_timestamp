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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_timestamp/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeTimestampBundle\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Date\ParseDateEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\AttributeTimestampBundle\Attribute\Timestamp;
use MetaModels\DcGeneral\Data\Model;

/**
 * Handles event operations for timestamp attributes.
 */
class BackendEventListener
{
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

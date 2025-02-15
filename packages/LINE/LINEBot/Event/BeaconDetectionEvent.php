<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace LINE\LINEBot\Event;

/**
 * A class that represents the event of beacon detection.
 *
 * @package LINE\LINEBot\Event
 */
class BeaconDetectionEvent extends BaseEvent
{
    /**
     * BeaconDetectionEvent constructor.
     *
     * @param array $event
     */
    public function __construct($event)
    {
        parent::__construct($event);
    }

    /**
     * Get hardware ID of the beacon.
     *
     * @return string
     */
    public function getHwid()
    {
        return $this->event['beacon']['hwid'];
    }

    /**
     * Returns type of beacon event.
     *
     * @return string
     */
    public function getBeaconEventType()
    {
        return $this->event['beacon']['type'];
    }

    /**
     * Returns device message of the beacon.
     *
     * @return string a binary string containing data
     */
    public function getDeviceMessage()
    {
        return pack('H*', $this->event['beacon']['dm']);
    }
}

<?php

/**
 * Copyright 2018 LINE Corporation
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

namespace LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder;

use LINE\LINEBot\Constant\Flex\ComponentIconAspectRatio;
use LINE\LINEBot\Constant\Flex\ComponentIconSize;
use LINE\LINEBot\Constant\Flex\ComponentMargin;
use LINE\LINEBot\Constant\Flex\ComponentType;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder;
use LINE\LINEBot\Util\BuildUtil;

/**
 * A builder class for icon component.
 *
 * @package LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder
 */
class IconComponentBuilder implements ComponentBuilder
{
    /** @var string */
    private $url;
    /** @var ComponentMargin */
    private $margin;
    /** @var ComponentIconSize */
    private $size;
    /** @var ComponentIconAspectRatio */
    private $aspectRatio;

    /** @var string */
    private $position;
    /** @var string */
    private $offsetTop;
    /** @var string */
    private $offsetBottom;
    /** @var string */
    private $offsetStart;
    /** @var string */
    private $offsetEnd;

    /** @var array */
    private $component;

    /**
     * IconComponentBuilder constructor.
     *
     * @param string $url
     * @param ComponentMargin|null $margin
     * @param ComponentIconSize|null $size
     * @param ComponentIconAspectRatio|null $aspectRatio
     */
    public function __construct($url, $margin = null, $size = null, $aspectRatio = null)
    {
        $this->url = $url;
        $this->margin = $margin;
        $this->size = $size;
        $this->aspectRatio = $aspectRatio;
    }

    /**
     * Create empty IconComponentBuilder.
     *
     * @return IconComponentBuilder
     */
    public static function builder()
    {
        return new self(null);
    }

    /**
     * Set url.
     *
     * @param string $url
     * @return IconComponentBuilder
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set margin.
     *
     * @param ComponentMargin|string|null $margin
     * @return IconComponentBuilder
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
        return $this;
    }

    /**
     * Set size.
     *
     * @param ComponentIconSize|string|null $size
     * @return IconComponentBuilder
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Set aspectRatio.
     *
     * @param ComponentIconAspectRatio|string|null $aspectRatio
     * @return IconComponentBuilder
     */
    public function setAspectRatio($aspectRatio)
    {
        $this->aspectRatio = $aspectRatio;
        return $this;
    }

    /**
     * Set position.
     *
     * specifiable relative or absolute
     *
     * @param string|ComponentPosition|null $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Set offsetTop.
     *
     * specifiable percentage, pixel and keyword.
     * (e.g.
     * percentage: 5%
     * pixel: 5px
     * keyword: none (defined in ComponentSpacing)
     *
     * @param string|ComponentSpacing|null $offsetTop
     * @return $this
     */
    public function setOffsetTop($offsetTop)
    {
        $this->offsetTop = $offsetTop;
        return $this;
    }
    
    /**
     * Set offsetBottom.
     *
     * specifiable percentage, pixel and keyword.
     * (e.g.
     * percentage: 5%
     * pixel: 5px
     * keyword: none (defined in ComponentSpacing)
     *
     * @param string|ComponentSpacing|null $offsetBottom
     * @return $this
     */
    public function setOffsetBottom($offsetBottom)
    {
        $this->offsetBottom = $offsetBottom;
        return $this;
    }
    
    /**
     * Set offsetStart.
     *
     * specifiable percentage, pixel and keyword.
     * (e.g.
     * percentage: 5%
     * pixel: 5px
     * keyword: none (defined in ComponentSpacing)
     *
     * @param string|ComponentSpacing|null $offsetStart
     * @return $this
     */
    public function setOffsetStart($offsetStart)
    {
        $this->offsetStart = $offsetStart;
        return $this;
    }
    
    /**
     * Set offsetEnd.
     *
     * specifiable percentage, pixel and keyword.
     * (e.g.
     * percentage: 5%
     * pixel: 5px
     * keyword: none (defined in ComponentSpacing)
     *
     * @param string|ComponentSpacing|null $offsetEnd
     * @return $this
     */
    public function setOffsetEnd($offsetEnd)
    {
        $this->offsetEnd = $offsetEnd;
        return $this;
    }

    /**
     * Builds icon component structure.
     *
     * @return array
     */
    public function build()
    {
        if (isset($this->component)) {
            return $this->component;
        }

        $this->component = BuildUtil::removeNullElements([
            'type' => ComponentType::ICON,
            'url' => $this->url,
            'margin' => $this->margin,
            'size' => $this->size,
            'aspectRatio' => $this->aspectRatio,
            'position' => $this->position,
            'offsetTop' => $this->offsetTop,
            'offsetBottom' => $this->offsetBottom,
            'offsetStart' => $this->offsetStart,
            'offsetEnd' => $this->offsetEnd,
        ]);

        return $this->component;
    }
}

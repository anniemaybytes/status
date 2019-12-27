<?php

namespace RunTracy\Helpers;

use Tracy\IBarPanel;

/**
 * Class SlimContainerPanel
 *
 * @package RunTracy\Helpers
 */
class SlimContainerPanel implements IBarPanel
{
    private $content;
    private $ver;
    private $icon;

    /**
     * SlimContainerPanel constructor.
     *
     * @param null $data
     * @param array $ver
     */
    public function __construct($data = null, array $ver = [])
    {
        $this->content = $data;
        $this->ver = $ver;
    }

    /**
     * @return string
     */
    public function getTab()
    {
        $this->icon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" id="Layer_1" x="0px" y="0px" ' .
            'viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="24px" ' .
            'height="24px"><path d="M446.537,188.13h-37.026l-101.798-70.979c7.453-27.802-7.912-55.925-33.853-65.' .
            '226V17.86C273.86,7.997,265.864,0,256,0    c-9.864,0-17.86,7.997-17.86,17.86v34.065c-25.961,9.309-41.' .
            '301,37.449-33.853,65.228L102.489,188.13H65.463    c-22.966,0-41.649,18.683-41.649,41.649v240.57c0,22.' .
            '967,18.683,41.651,41.649,41.651h381.072    c22.967,0,41.651-18.683,41.651-41.649V229.78C488.186,206.' .
            '813,469.503,188.13,446.537,188.13z M160.744,400.074    c0,9.864-7.997,17.86-17.86,17.86c-9.864,0-17.' .
            '86-7.997-17.86-17.86V300.056c0-9.864,7.997-17.86,17.86-17.86    c9.864,0,17.86,7.997,17.86,17.86V400.' .
            '074z M273.86,400.074c0,9.864-7.997,17.86-17.86,17.86c-9.864,0-17.86-7.997-17.86-17.86 V300.056c0-9.' .
            '864,7.997-17.86,17.86-17.86c9.864,0,17.86,7.997,17.86,17.86V400.074z M164.945,188.13l59.43-41.438' .
            ' c18.602,13.801,44.186,14.144,63.25,0l59.43,41.438H164.945z M386.977,400.074c0,9.864-7.997,17.86-17.' .
            '86,17.86 c-9.864,0-17.86-7.997-17.86-17.86V300.056c0-9.864,7.997-17.86,17.86-17.86c9.864,0,17.86,7.' .
            '997,17.86,17.86V400.074z" fill="#006DF0"/></svg>';
        return '<span title="Slim Container">' . $this->icon . '</span>';
    }

    /**
     * @return string
     */
    public function getPanel()
    {
        return '<h1>' . $this->icon . ' Slim ' . $this->ver['slim'] . ' Container:</h1>
        <div style="overflow: scroll; max-height: 600px;">' . $this->content . '</div>';
    }
}

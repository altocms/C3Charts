<?php

namespace Alto\C3Charts;

/**
 * Class Axis
 *
 * @package Alto\C3Charts
 */
class Axis extends Entity {

    protected $bShow = null;
    protected $sLabelText;
    protected $sLabelPosition;
    protected $oTick;
    protected $aData;

    public function __construct() {

        $this->oTick = new Entity();
    }

    public function setLabelText($sLabel) {

        $this->sLabelText = (string)$sLabel;
        return $this;
    }

    public function setLabel($xLabel) {

        if (is_scalar($xLabel)) {
            return $this->setLabelText($xLabel);
        }
        $aData = (array)$xLabel;
        $this->setLabelText(reset($aData));
        $this->setLabelPosition(next($aData));

        return $this;
    }

    public function getLabelText() {

        return $this->sLabelText;
    }

    public function setLabelPosition($sPosition) {

        $this->sLabelPosition = (string)$sPosition;
        return $this;
    }

    public function setPosition($sPosition) {

        return $this->setLabelPosition($sPosition);
    }

    public function getLabelPosition() {

        return $this->sLabelPosition;
    }

    public function setShow($bShow = true) {

        $this->bShow = (bool)$bShow;
        return $this;
    }

    public function getShow() {

        return $this->bShow;
    }

    public function setData($aData) {

        if (is_scalar($aData)) {
            $this->aData = (array)$aData;
        } else {
            $this->aData = (array)$aData;
        }
        return $this;
    }

    public function getData() {

        return $this->aData;
    }

    public function setType($sType) {

        return $this->setProp('type', $sType);
    }

    public function getType() {

        return $this->getProp('type');
    }

    public function setFormat($sFormat) {

        $this->oTick->setProp('format', $sFormat);
        return $this;
    }

    public function getFormat() {

        $this->oTick->getProp('format');
    }

    public function setTimeSeries($aData) {

        $this->setType('timeseries')->setData($aData)->setFormat('%Y-%m-%d');
        return $this;
    }

    public function setCategories($aData) {

        $this->setType('category')->setData($aData);
        return $this;
    }

    public function asArray() {

        $aResult = [];
        if (!is_null($this->bShow)) {
            $aResult['show'] = $this->bShow;
        }
        if ($this->sLabelText) {
            $aResult['label'] = ['text' => $this->sLabelText];
            if ($this->sLabelPosition) {
                $aResult['label'] = ['text' => $this->sLabelText, 'position' => $this->sLabelPosition];
            } else {
                $aResult['label'] = $this->sLabelText;
            }
        }
        if ($sType = $this->getType()) {
            $aResult['type'] = $sType;
        }
        if (($sType == 'category') && ($aData = $this->getData())) {
            $aResult['categories'] = $aData;
        }
        if ($aTick = $this->oTick->asArray()) {
            $aResult['tick'] = $aTick;
        }
        return $aResult;
    }
}

// EOF
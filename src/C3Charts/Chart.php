<?php

namespace Alto\C3Charts;

/**
 * Class Chart
 *
 * @package Alto\C3Charts
 */
class Chart extends Entity{

    /** @var Column[] */
    protected $aColumns = [];

    /** @var Axis[] */
    protected $aAxes = [];

    protected $aGroups = [];

    protected $sChartType;

    protected $aChartOptions = [];

    public function clearColumns() {

        $this->aColumns = [];
    }

    public function setColumn($sName, $aData = []) {

        if ((func_num_args() == 1) && ($sName instanceof Column)) {
            $oColumn = $sName;
            $sName = $oColumn->getName();
        } elseif ($aData instanceof Column) {
            $oColumn = $aData;
        } else {
            $oColumn = new Column($sName, $aData);
        }
        $this->aColumns[$sName] = $oColumn;

        return $oColumn;
    }

    public function getColumns() {

        return $this->aColumns;
    }

    public function getColumn($sName) {

        $aColumns = $this->getColumns();
        if (isset($aColumns[$sName])) {
            return $aColumns[$sName];
        }
        return null;
    }

    public function getColumnByIndex($iIndex) {

        $aColumns = array_values($this->getColumns());
        if (isset($aColumns[$iIndex])) {
            return $aColumns[$iIndex];
        }
        return null;
    }

    /**
     * @param string $sType
     * @param string $sLabel
     *
     * @return Axis
     */
    public function setAxis($sType, $sLabel = null) {

        if (empty($this->aAxes[$sType])) {
            $this->aAxes[$sType] = new Axis();
        }
        if ($sLabel === false) {
            $this->aAxes[$sType]->setShow(false);
        } elseif (($sLabel === true) || ($sLabel === null)) {
            $this->aAxes[$sType]->setShow(true);
        } else {
            $this->aAxes[$sType]->setLabel($sLabel);
        }
        return $this->aAxes[$sType];
    }

    /**
     * @param string $sLabel
     *
     * @return Axis
     */
    public function setX($sLabel = null) {

        return $this->setAxis('x', $sLabel);
    }

    /**
     * @param array $aData
     *
     * @return Axis
     */
    public function setXData($aData) {

        return $this->setX()->setData($aData);
    }

    public function setXTimeSeries($aData) {

        return $this->setX()->setTimeSeries($aData);
    }

    public function setXCategories($aData) {

        return $this->setX()->setCategories($aData);
    }

    public function setY($sLabel = null) {

        return $this->setAxis('y', $sLabel);
    }

    public function setY1($sLabel = null) {

        return $this->setY($sLabel);
    }

    public function setY2($sLabel = null) {

        return $this->setAxis('y2', $sLabel);
    }

    public function setGroup() {

        $aGroup = [];
        foreach(func_get_args() as $sArg) {
            $aGroup[] = (string)$sArg;
        }
        $this->aGroups[] = $aGroup;
        return $this;
    }

    public function getGroups() {

        return $this->aGroups;
    }

    public function setChartType($sChartType, $aOptions = []) {

        $this->sChartType = $sChartType;
        $this->aChartOptions[$sChartType] = $aOptions;
        return $this;
    }

    public function getChartType() {

        return $this->sChartType;
    }

    public function getChartOptions($sChartType = null) {

        if (!$sChartType) {
            return $this->aChartOptions;
        } elseif(isset($this->aChartOptions[$sChartType])) {
            return $this->aChartOptions[$sChartType];
        }
        return null;
    }

    public function addRow($aRow) {

        foreach($aRow as $sName => $xItem) {
            $oColumn = $this->getColumn($sName);
            if ($oColumn) {
                $oColumn->addRow($xItem);
            }
        }
    }

    public function slice($iOffset = 0, $iLength = 1) {

        $oNewChart = clone $this;
        $oNewChart->clearColumns();
        foreach($this->getColumns() as $sName => $oColumn) {
            $oNewChart->setColumn($sName, $oColumn->slice($iOffset, $iLength));
        }
        return $oNewChart;
    }

    public function asArray() {

        $aResult = [];
        if (!empty($this->aAxes['x']) && $this->aAxes['x']->getType() == 'timeseries') {
            $aResult['data']['x'] = 'x';
            $aResult['data']['columns'][] = array_merge(['x'], $this->aAxes['x']->getData());
        }
        $aXs = [];
        $bXs = false;
        $i = 0;
        foreach ($this->aColumns as $oColumn) {
            $aXData = $oColumn->getXData();
            $sX = ('x' . ++$i);
            $aXs['data']['xs'][$oColumn->getName()] = $sX;
            if ($aXData) {
                $aXs['data']['columns'][] = array_merge([$sX], $aXData);
                $bXs = true;
            } else {
                $aXs['data']['columns'][] = [];
            }
        }
        if ($bXs) {
            $aResult = $aXs;
        }

        $aResult['data']['type'] = [];
        foreach ($this->aColumns as $oColumn) {
            $aResult['data']['columns'][] = $oColumn->asArray();
            if ($oColumn->isY2()) {
                $aResult['data']['axes'][$oColumn->getName()] = 'y2';
                $this->setY2();
            }
            if ($sType = $oColumn->getType()) {
                $aResult['data']['types'][$oColumn->getName()] = $sType;
            }
            if ($oColumn->getRegions()) {
                $aResult['data']['regions'][$oColumn->getName()] = $oColumn->getRegionsData();
            }
        }
        foreach($this->getGroups() as $aGroup) {
            foreach($aGroup as $iIndex => $sColumn) {
                if (!isset($this->aColumns[$sColumn])) {
                    unset($aGroup[$iIndex]);
                }
            }
            if ($aGroup) {
                $aResult['data']['groups'][] = $aGroup;
            }
        }
        if ($sChartType = $this->getChartType()) {
            $aResult['data']['type'] = $sChartType;
            if ($aOptions = $this->getChartOptions($sChartType)) {
                $aResult[$sChartType] = $aOptions;
            }
        } else {
            unset($aResult['data']['type']);
        }
        foreach($this->aAxes as $sType => $oAxis) {
            $aAxis = $oAxis->asArray();
            if ($aAxis) {
                $aResult['axis'][$sType] = $aAxis;
            }
        }
        return $aResult;
    }

}

// EOF
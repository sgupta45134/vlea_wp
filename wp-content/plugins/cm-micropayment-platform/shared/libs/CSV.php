<?php

class CSVExport
{
    protected $_columns = array();
    protected $_data = array();

    public function setColumns($columns)
    {
        $this->_columns = $columns;
    }

    public function setData($data)
    {
        $this->_data = array_merge($this->_data, $data);
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    public function getData()
    {
        return $this->_data;
    }

    public function download()
    {
        header("Content-type: text/csv");
        header("Content-disposition: attachment; filename=" . date("Y-m-d").".csv");
        header("Pragma: no-cache");

        echo $this->prepareData();
    }

    public function prepareData()
    {
        $aCSV = array();
        foreach($this->getColumns() as $aRow)
        {
            $aCSV[] = implode(',', $aRow);
        }

        foreach($this->getData() as $aRow)
        {
            $aCSV[] = implode(',', $aRow);
        }

        return implode("\n", $aCSV);
    }
}

<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Export;

/**
 *
 * PHPExcel 5 Export (97-2003) (.xls)
 * 52 columns maximum
 *
 */
class PHPExcel5Export extends Export
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    public $objPHPExcel;

    protected $prepareWriterCallback = null;

    protected $preparePhpExcelCallback = null;

    public function __construct($tilte, $fileName = 'export', $params = array(), $charset = 'UTF-8')
    {
        $this->objPHPExcel =  new \PHPExcel();
        $this->objPHPExcel->getProperties()->setTitle($tilte);

        parent::__construct($tilte, $fileName, $params, $charset);
    }

    public function computeData($grid)
    {
        $data = $this->getFlatGridData($grid);

        $row = 0;
        $activeSheet = $this->objPHPExcel->getActiveSheet();
        foreach ($data as $line) {
            $row++;
            $column = 'A';
            foreach ($line as $cell) {
                $activeSheet->SetCellValue($column.$row, $cell);
                if ($row == 1) {
                    $activeSheet->getColumnDimension($column)->setAutoSize(true);
                }

                // 52 columns maximum
                if ($column == 'Z') {
                    $column = 'AA';
                } else {
                    $column++;
                }
            }
        }

        $this->preparePhpExcel($row, $activeSheet->getHighestColumn());

        $objWriter = $this->getWriter();

        $this->prepareWriter($objWriter, $row, $activeSheet->getHighestColumn());

        ob_start();

        $objWriter->save("php://output");

        $this->content = ob_get_contents();

        ob_end_clean();
    }

    protected function getWriter()
    {
        return new \PHPExcel_Writer_Excel5($this->objPHPExcel);
    }

    /**
     * @param \Closure $callback
     */
    public function manipulatePhpExcel(\Closure $callback = null)
    {
        $this->preparePhpExcelCallback = $callback;

        return $this;
    }

    /**
     * @param \Closure $callback
     */
    public function manipulateWriter(\Closure $callback = null)
    {
        $this->prepareWriterCallback = $callback;

        return $this;
    }

    protected function preparePhpExcel($rowCount, $lastColumn)
    {
        if (is_callable($this->preparePhpExcelCallback)) {
            call_user_func($this->preparePhpExcelCallback, $this->objPHPExcel, $rowCount, $lastColumn);
        }
    }

    protected function prepareWriter(\PHPExcel_Writer_IWriter $writer, $rowCount, $lastColumn)
    {
        if (is_callable($this->prepareWriterCallback)) {
            call_user_func($this->prepareWriterCallback, $writer, $rowCount, $lastColumn);
        }
    }
}

<?php
    
    namespace YS\Export;
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    
    class Xls extends ExportHandler
    {
        /**
         * To hold current active spreadsheet instance
         */
        protected $sheet;
    
        /**
         * To hold PhpSpreadsheet Writer instance
         */
        protected $writer;
    
        /**
         * styles to be applied on heading row
         * @var array
         */
        protected $style = [];
        
        /** @var string  $ext extension of file */
        protected  $ext = ".xls";
    
        /**
         * initialize new spreadsheet in a new workbook
         * and set current active sheet
         */
        protected function openFileStream()
        {
            $this->file = new Spreadsheet();
            
            $this->writer = new Xlsx( $this->file );
            
            $this->sheet = $this->file->getActiveSheet();
        }
    
        /**
         * Store newly created excel file in a
         * tem storage and free excel writer and reader
         */
        protected function closeFileStream()
        {
            $this->writer->save($this->filepath);
            
            unset( $this->file, $this->writer, $this->sheet );
        }
        
        /**
         * sanitize column values before they get added to file to export
         * @param array $column
         * @return array
         */
        protected function sanitizedValues( array $column)
        {
             return array_map( function( $string ) {
                // escape tab characters
                $string = preg_replace("/\t/", "\\t", $string);
    
                // escape new lines
                $string = preg_replace("/\r?\n/", "\\n", $string);
    
                parent::sanitize( $string );
                
                return $string;
            }, $column );
           
        }
        
        /**
         * Configure styling of excel file to download
         * @param string $endCell highest cell name
         * @return void
         * @throws \PhpOffice\PhpSpreadsheet\Exception
         */
        protected function styleCell(string $endCell)
        {
            $this->sheet->mergeCells("A1:{$endCell}1")
                ->setCellValue("A1",config('app.name').'-'. ucfirst( $this->filename) );
            
            $this->sheet->getStyle("A1")
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            $this->sheet->getStyle("A2:{$endCell}2")->applyFromArray($this->getStyle());
            
            $this->sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 2);
            
            $this->sheet->getStyle("A:$endCell")->getAlignment()->setWrapText(true);
            
            $this->writer->setOffice2003Compatibility(true);
        }
        
        /**
         * Set Excel Heading column row
         * @throws \PhpOffice\PhpSpreadsheet\Exception
         */
        protected function setColumnHeadings()
        {
           $this->headers = array_values($this->headers);
            // set the column headers
            $this->sheet->fromArray($this->headers,NULL,"A2");
            
            $row =  $this->sheet->getRowIterator(2)->current();
            $i=0;
            foreach ($row->getCellIterator() as $k => $cell)
            {
                $cell->setValue($this->headers[$i]);
                $this->sheet->getColumnDimension($k)->setAutoSize(true);
                $i++;
            }
            //cell styling is done here
            $this->styleCell( $k );
        }
        
        /**
         * styles to be applied on heading column row
         * of excel sheet to be downloaded
         * @return array
         */
        public function getStyle()
        {
            return  !empty( $this->style) ? $this->style : $this->getDefaultStyle();
        }
    
        /**
         * styles to be applied on heading column row
         * of excel sheet to be downloaded
         * @param array $style
         * @return array
         */
        public function setStyle( array $style )
        {
            array_merge( $this->getDefaultStyle(),  $style );
        }
        
        /**
         * Default styling to be applied on heading row
         * @return array
         */
        protected function getDefaultStyle()
        {
            return  [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => [
                        'argb' => 'FFFFFF'
                    ]
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => [
                            'argb' => '404040'
                        ],
                    ]
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => [
                        'argb' => '3280b9',
                    ],
                ],
            ];
        }
        
        /**
         * Add data to csv rows
         *
         * @return void
         */
        public function addCells()
        {
            $length = 0;
            $this->query->get()->chunk(1000)
            ->each ( function( $results ) use(&$length) {
                foreach($results as $k => $r ){
                    $row = $k+3;$length++;
                    $this->sheet->fromArray( $this->sanitize ? $this->sanitizedValues( (array) $r ) : (array) $r, NULL, "A{$row}" );
                }
            });
            $this->totalRecords = $length;
        }
    }

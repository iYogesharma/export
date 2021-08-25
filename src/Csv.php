<?php
    
    namespace YS\Export;
    
    class Csv extends ExportHandler
    {
        protected $length;
        
        /**
         * Set excel compatibility
         */
        protected function setExcelCompatibility()
        {
            fputs($this->file , $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
        }
        
        /**
         * Set csv column headings
         *
         * @return void
         */
        public function setColumnHeadings()
        {
            $this->setExcelCompatibility();
            // send the column headers
            fputcsv( $this->file, $this->headers, "," );
        }
    
        /**
         * sanitize column values before they get added to file to export
         * @param array $column
         * @return array
         */
        protected function sanitizedValues( array $column)
        {
            return array_map( function( $string ) {
                parent::sanitize( $string );
                return $string;
            }, $column );
        
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
            ->each ( function( $results ) use(&$length)  {
                foreach($results as $r){
                    fputcsv( $this->file, $this->sanitize ? $this->sanitizedValues( (array) $r ) : (array) $r, "," );
                }
                $length++;
            });
            $this->totalRecords = $length;
        }
    }

<?php
    
    namespace YS\Export;

    class Json extends ExportHandler
    {
        /** Whether to set column headings or not@var bool */
        protected  $heading = false;
    
        /** @var string  $ext extension of file */
        protected  $ext = ".json";
    
        /**
         * Add data to json file
         *
         * @return void
         */
        public function addCells()
        {
            $length = 0;
            $this->query->get()->chunk(1000)
                ->each ( function( $results ) use(&$length) {
                    fwrite( $this->file, json_encode( $results ) );
                });
            $this->totalRecords = $length;
        }
        
        
    }

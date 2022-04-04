<?php

    namespace YS\Export;

    use YS\Datatable\AbstractDatatable;
    use YS\Export\IncorrectDataSourceException;
    use Illuminate\Support\Facades\Schema;
    use Closure;

    abstract class ExportHandler implements ExportHandlerInterface
    {
        /** Instance of query builder */
        protected  $query;

        /** Hold column headings */
        protected $headers;

        /** Whether to set column headings or not@var bool */
        protected  $heading = true;

        /** query column names */
        protected $columns;

        /** Reference to the file */
        protected $file;

        /** @var string Default file name */
        protected  $filename = "report";

        /** @var string temp storage path of file */
        protected  $filepath;

        /** @var string  $ext extension of file */
        protected  $ext = ".csv";

        /** whether to sanitize the column values */
        protected  $sanitize = false;

        /** @var int Total number of records in result */
        protected $totalRecords = 0;

        /** @var string  $result query result */
        protected  $result;

        /**
         * ExportHandler constructor.
         * @param object $source
         * @param null $filename
         * @param null $headers
         * @param bool $sanitize
         *
         * @throws IncorrectDataSourceException
         */
        public function __construct( $source, $export = true, $filename=null,  $headers = null, $sanitize = false )
        {
            $this->setQuery( $source );

            $this->filename = $filename ?? $this->getFileName();

            $this->filepath = tempnam(sys_get_temp_dir(), "{$this->ext}_");

            $this->sanitize  = $sanitize;

            $this->guessColumnsIfAstrixInSelectStatement();

            $this->setHeaderAndColumns( $headers );

            $this->result = $this->query->get();

            $export ? $this->createExport( $filename ) : '';
        }

        /**
         * File name to export
         *
         * @return string
         */
        public  function getFileName()
        {
            return $this->filename;
        }

        /**
         * File to export
         *
         * @return string
         */
        public function getFile()
        {
            return $this->file;
        }

        /**
         * File to export
         *
         * @return string
         */
        public function getPath()
        {
            return $this->filepath;
        }

        /**
         * add data in file to be exported
         * @return this
         */
        public function export()
        {
            $this->createExport();

            $this->closeFileStream();

            return $this;
        }

        /**
         * Set @property $query of class
         * @param $source
         *
         * @return void
         * @throws IncorrectDataSourceException
         */
        protected function setQuery( $source )
        {
            if( $source instanceof \Illuminate\Database\Query\Builder )
            {
                $this->query = $source;

            }
            else if( $source instanceof \Illuminate\Database\Eloquent\Builder )
            {
                $this->query = $source->getQuery();
            }
            else
            {
                throw new IncorrectDataSourceException(
                    "Data source  must be instance of either \Illuminate\Database\Query\Builder or \Illuminate\Database\Eloquent\Builde"
                );
            }
        }

        /**
         * get query result
         * @return array
         */
        public function getResult()
        {
            return $this->result->toArray();
        }

        /**
         * Total number of records found in result
         * @return int
         */
        public function count()
        {
            return $this->totalRecords;
        }

        /**
         * Set headers and column names to use in query
         * for the sheet to export
         * @param array|null $headers
         *
         * @return void
         */
        protected function setHeaderAndColumns( $headers = null )
        {
            if ( $headers )
            {
                $this->guessColumnNames( $headers );
            }
            else
            {
                $this->guessColumnNamesAndHeaders();
            }

            /** The delete columns that are no longer needed in the exported sheet */
            $this->deleteUnwantedKeys();
        }

        /**
         * guess column names if "table.*" is present in select statement
         * and add those columns to query select statement.
         * @return void
         */
        protected function guessColumnsIfAstrixInSelectStatement()
        {
            if(  empty($this->query->columns)  ||  ( count ( $this->query->columns ) === 1 && $this->query->columns[0] === '*' ) )
            {
                $this->query->columns = Schema::getColumnListing( $this->query->from );
            }
            else
            {
                foreach( $this->query->columns as $k => $c )
                {
                    if( strpos($c,'.*'))
                    {
                        unset($this->query->columns[$k]);

                        $this->addTableColumnsInQuery( $c );

                    }
                    else if ( trim($c ) === '*' )
                    {
                        unset($this->query->columns[$k]);
                    }
                }
            }

        }

        /**
         * Begin the process of exporting data
         * to desired file format
         *
         * @return self
         */
        protected function createExport()
        {

            $this->openFileStream();

            /** Set Column Headings Of File  */
            $this->heading ? $this->setColumnHeadings() : null ;

            /** Insert Data In The File */
            $this->addCells();

        }

        /**
         * Set pointer to the file
         *
         * @return void
         */
        protected function openFileStream()
        {
            // create a file pointer connected to the output stream
            $this->file = fopen($this->filepath, 'w');
        }

        /**
         * Unset file pointer
         *
         * @return void
         */
        protected function closeFileStream()
        {
            // create a file pointer connected to the output stream
            fclose( $this->file );
        }

        /**
         * Guess column names and headers from @property query
         *
         * @return void
         */
        protected function guessColumnNamesAndHeaders()
        {
            foreach( $this->query->columns as $k => $c )
            {
                if(!strpos($c,'_id') && !strpos($c,'.*'))
                {
                    $column = trim($this->getQualifiedColumnName( $c ));

                    if( $this->heading )
                    {
                        $this->sanitize($column );
                        $this->headers[]= heading_case($column);
                    }

                }
                else
                {
                    unset ( $this->query->columns[$k] );
                }

            }
        }

        /**
         * Guess column names from @property query
         * @param array $headers
         *
         * @return void
         */
        protected function guessColumnNames( array $headers )
        {
            if( $this->heading )
            {
                $this->headers  =  $headers;
                array_walk($this->headers, call_user_func( [ $this, 'sanitize' ]) );
            }

            foreach( $this->query->columns as $k => $c )
            {
                if( strpos($c,'_id') || strpos($c,'.*'))
                {
                    unset ( $this->query->columns[$k] );
                }
            }
        }

        /**
         * if * is given in select statement in query get
         * all columns from schema using table name provided with *
         * add add those columns to query columns
         * @param string $column
         * @return void
         */
        protected function addTableColumnsInQuery( $column )
        {
            $table = explode('.*',$column)[0];

            $columns = Schema::getColumnListing( $table );

            delete_keys($columns, config('ys-export.skip'));

            $label = str_singular( $table );// to differentiate columns name in case two columns with same name

            array_walk($columns, function(&$value)use($table,$label) { $value = "{$table}.{$value} as {$label}_{$value}"; } );

            $this->query->columns = array_merge(
                $this->query->columns,
                $columns
            );
        }

        /**
         * Get name of column from query
         * @param string $name
         * @return string
         */
        protected function getQualifiedColumnName( string $name )
        {
            if(strpos($name,' as '))
            {
                $column = explode(' as ',$name)[1];

            }
            else
            {
                if(isset(explode('.',$name)[1]))
                {
                    $column = explode('.',$name)[1];;

                }
                else
                {
                    $column = $name;
                }
            }
            return $column;
        }

        /**
         * Add/edit column details of export
         *
         * @param string column name
         * @param Closure
         *
         * @return $this
         */
        public function add($column, Closure $closure)
        {
            foreach ($this->result as $r) {
                $r->$column = $closure->call($this, $r);
            }
            return $this;
        }

        /**
         * Add/edit  details of multiple columns of export
         *
         * @param array $column
         *
         * @return $this
         */
        public function addColumns(array $column)
        {
            foreach ($column as $c => $cols) {
                foreach ($this->result as $r) {
                    $r->$c = $cols->call($this, $r);
                }

            }
            return $this;
        }

        /**
         * Insert Data In The File
         *
         * @return void
         */
        abstract public function addCells();

        /**
         * Unset the query column that we don't want to export
         *
         * @return void
         */
        protected function deleteUnwantedKeys()
        {
            if ( $this->heading ) {
                delete_keys($this->headers, array_map('ucwords', str_replace( "_", " ", config('ys-export.skip') , $i ) ) );
            }
            delete_keys($this->query->columns, config('ys-export.skip'));

        }

        /**
         * return response based on type of request
         * @return mixed
         */
        public function response()
        {
            $this->closeFileStream();

            if( $this->totalRecords > 3000 )
            {
                return streamDownload( $this->filepath, $this->filename, $this->ext );
            }
            else
            {
                return response()->download($this->filepath,"$this->filename$this->ext")->deleteFileAfterSend(true);
            }
        }

        //Original PHP code by Chirp Internet: www.chirpinternet.eu

        /**
         * sanitize column values before they get added to file to export
         * @param $string
         */
        protected function sanitize( &$string )
        {
            if($string == '0') $string = 'NO';
            if($string == '1') $string = 'YES';
            if(
                // force certain number/date formats to be imported as strings
                preg_match("/^0/", $string) ||
                preg_match("/^\+?\d{8,}$/", $string) ||
                preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $string))
            {
                $string = " $string ";
            }
            // escape fields that include double quotes
            if(strstr($string, '"'))
            {
                $string = '"' . str_replace('"', '""', $string) . '"';
            }
        }
    }

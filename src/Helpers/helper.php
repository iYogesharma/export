<?php
    
    
    if(!function_exists('heading_case')) {
        /**
         * Convert string to custom heading case
         * @example contact_number to Contact Number
         * @return string
         */
        function heading_case( string $string , $slug = '_'){
            return title_case(str_replace( $slug, ' ', trim($string)));
        }
    }
    
    
    if(!function_exists('delete_key')) {
        /**
         * Convert string to custom heading case
         * @param $array reference to array
         * @param tring $value
         * @return void
         */
        function delete_key( &$array , string $value ){
            
            if (($key = array_search($value, $array)) !== false) {
                unset($array[$key]);
            }
            
        }
    }
    
    if(!function_exists('delete_keys')) {
        /**
         * Delete specific keys from array by value
         * @param $array reference to array
         * @param array $values
         * @return string
         */
        function delete_keys( &$array , array $values ){
            
            foreach($values as $value )
            {
                delete_key( $array , $value );
            }
            
        }
    }
    
    if(!function_exists('streamDownload')) {
        /**
         * Stream larger files
         * @param $filepath
         * @param $filename
         * @param $ext
         * @return \Symfony\Component\HttpFoundation\StreamedResponse
         */
        function streamDownload(  string $filepath, string $filename, string $ext  ){
    
            return response()->streamDownload(function () use (  $filepath, $filename, $ext ) {
                $file = fopen($filepath, 'rb');
        
                while (! feof($file)) {
                    echo fread($file, 2048);
                }
                fclose($file);
                //delete file from tem directory
                unlink($filepath);
        
            }, "{$filename}.{$ext}");
            
        }
    }


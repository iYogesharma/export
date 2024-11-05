<?php

namespace YS\Export;

/**
 * @author  @iyogesharma
 * @dated Nov 5,2024
*/

class ArrayToXls extends Xls
{
    /**
     * ArrayToXls constructor.
     * @param bool $export
     * @param object $source
     * @param null $filename
     * @param null $headers
     * @param bool $sanitize
     */
    public function __construct( $source, $export = true, $filename=null,  $headers = null, $sanitize = false )
    {
        $this->filename = $filename ?? $this->getFileName();

        $this->filepath = tempnam(sys_get_temp_dir(), "{$this->ext}_");

        $this->sanitize  = $sanitize;

        $this->headers = $headers;
   
        $this->result = $this->formattedResult( $source, $this->formattedHeaders($headers) );
     
        $export ? $this->createExport( $filename ) : '';
    }


    /**
     * Format headers in the format that we need for
     * comparing header keys
     * @param Request $request
     * @return mixed
     */
    public function formattedHeaders($headers)
    {
        array_walk($headers, function (&$value) {
            $value = explode('(', $value)[0];
            $value = str_replace('*', '', $value);
            $value = strtolower(str_replace(' ', '_', $value));
        });
        return $headers;
    }

    /**
     * format result to return selected values
     * based on @param $headers
     *
     * @param array $source
     * @param array $headers
     * @return Collection
     */
    public function formattedResult( $source, $headers)
    {
        $result = [];
        foreach ( $source as $i => $data )
        {
            foreach($headers as $header )
            {
                if( isset($data[$header]) || $data[$header] == null)
                {
                    $result[$i][$header] = $data[$header];
                }
            }
        }
        $source= null;
        return collect($result);
    }
}
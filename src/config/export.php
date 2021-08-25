<?php
    
    return [
    
        /*
       |--------------------------------------------------------------------------
       | Skip
       |--------------------------------------------------------------------------
       |
       | The column names that you don't want to show in the exported file .
       | Default are active and id you can add more columns here or remove
       | if you want to show them on exported file
       |
        */
    
        "skip" => [
            'active',
            'id',
            'password',
            'remember_token',
            'deleted_at',
            '_token',
            'api_token',
            'device_id'
        ]
    ];

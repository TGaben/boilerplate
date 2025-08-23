<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Documentation Path
    |--------------------------------------------------------------------------
    |
    | This value is the path to the documentation directory containing
    | the markdown files to be imported into the searchable database.
    |
    */

    'path' => env('DOCS_PATH', base_path('docs')),
];

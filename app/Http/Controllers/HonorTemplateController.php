<?php

namespace App\Http\Controllers;

use App\Exports\HonorTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HonorTemplateController extends Controller
{
    /**
     * Menangani permintaan unduh untuk template honor.
     */
    public function download()
    {
        return Excel::download(new HonorTemplateExport, 'template_import_honor.xlsx');
    }
}

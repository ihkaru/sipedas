<?php

namespace App\Http\Controllers;

use App\Exports\MitraTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MitraTemplateController extends Controller
{
    public function download()
    {
        return Excel::download(new MitraTemplateExport, 'template_import_mitra.xlsx');
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\KegiatanManmitExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportController extends Controller
{
    public function downloadKegiatanTemplate()
    {
        return Excel::download(new KegiatanManmitExport, 'template_kegiatan_manmit.xlsx');
    }
}

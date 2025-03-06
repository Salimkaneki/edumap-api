<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\EtablissementImport;
use Maatwebsite\Excel\Facades\Excel;

class EtablissementController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new EtablissementImport, $request->file('file'));

        return response()->json(['message' => 'Importation réussie !'], 200);
    }
}

<?php

namespace App\Imports;

use App\Models\Etablissement;
use Maatwebsite\Excel\Concerns\ToModel;

class EtablissementImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Etablissement([
            //
        ]);
    }
}

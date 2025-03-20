<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Http\Request;



class LanguageServices {

    function __construct()
    {

    }




    public function search_lang($request){
        return Language::whereNotIn('id',$request->notSearch)
                        ->where('name','like','%'.$request->search.'%')
                        ->limit(5)->get();
    }

    public function getLangByISO3Code($code){
        return Language::where('iso3_code',$code)
                        ->first();
    }
}

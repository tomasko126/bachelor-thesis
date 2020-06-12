<?php

namespace App\ImportModels;

use Illuminate\Database\Eloquent\Model;

class Pp_zadosti extends Model
{
    protected $table = 'pp_zadosti';

    public static function convertCreatedAt($request) {
        return !empty($request->datum_odeslani) ? $request->datum_odeslani : null;
    }

    public static function convertUpdatedAt($request) {
        return !empty($request->cas_odpovedi) ? $request->cas_odpovedi : null;
    }

    public static function convertState($request) {
        switch ($request->stav) {
            case 'SCHVALENO': return 'Approved';
            case 'POSLANO': return 'Sent';
            case 'ZAMITNUTO': return 'Rejected';
            default: return null;
        }
    }

    public static function convertRegistrationNumber($request) {
        return $request->typ_vrhu . ' ' . $request->reg_cislo_vrhu . '-' . $request->rok_reg;
    }

    public static function convertBreederNote($request) {
        return !empty($request->poznamka_chovatel) ? $request->poznamka_chovatel : null;
    }

    public static function convertRegistratorNote($request) {
        return !empty($request->komentar) ? $request->komentar : null;
    }
}

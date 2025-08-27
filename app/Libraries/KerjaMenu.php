<?php
namespace App\Libraries;

class KerjaMenu
{
    public static function get(): array
    {
        return [

            'ppat' => [
                ['slug' => 'ajb', 'title' => 'AJB (Akta Jual Beli)'],
                ['slug' => 'hibah', 'title' => 'Hibah'],
                ['slug' => 'turun-waris', 'title' => 'Turun Waris'],
                ['slug' => 'apht', 'title' => 'APHT'],
                ['slug' => 'ppjb', 'title' => 'PPJB'],
            ],
            'notaris' => [
                ['slug' => 'cv', 'title' => 'CV'],
                ['slug' => 'pt', 'title' => 'PT'],
                ['slug' => 'pergantian-pengurus', 'title' => 'Pergantian Pengurus'],
                ['slug' => 'pjb', 'title' => 'PJB'],
                ['slug' => 'skmht', 'title' => 'SKMHT'],
                ['slug' => 'waarmerking', 'title' => 'Waarmerking'],
                ['slug' => 'legalisasi', 'title' => 'Legalisasi'],
            ],
        ];
    }
}
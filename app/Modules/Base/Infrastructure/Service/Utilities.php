<?php

namespace App\Modules\Base\Infrastructure\Service;

class Utilities
{
    static public function clearName($name): string
    {
        // Reemplazar comillas simples por un acento sin letra
        $name = str_replace("'", "´", $name);

        // Reemplazar comillas dobles por dos acentos sin letra
        $name = str_replace('"', "´´", $name);

        // Reemplazar la "ç" por "c"
        $name = str_replace(["ç", "Ç"], ["c", "C"], $name);

        // Reemplazar la "ç" por "c"
        $name = str_replace(["ñ", "Ñ"], ["n", "N"], $name);

        // Reemplazar la "ç" por "c"
        $name = str_replace(["ï", "Ï"], ["i", "I"], $name);

        // Reemplazar la "ç" por "c"
        $name = str_replace(["ü", "Ü"], ["u", "U"], $name);

        // Eliminar caracteres raros, permitiendo letras, números sin vocales acentuadas en castellano y catalán
        $name = preg_replace(
            '/[^a-zA-Z0-9\-_\s´]/u',
            '',
            $name
        );

        return $name;
    }

    static public function generateRandomPassword()
    {
        $string = "";
        $lengthStep1 = '4'; // $chars
        $lengthStep2 = '1'; // $specialChars
        $lengthStep3 = '1'; // $lettersMayus
        $lengthStep4 = '1'; // $lettersMinus
        $lengthStep5 = '1'; // $numbers
        $lengthStep6 = '1'; // $specialChars
        $lengthStep7 = '1'; // $lettersMinus
        $lengthStep8 = '1'; // $numbers
        $lengthStep9 = '1'; // $lettersMayus
        $lettersMinus = 'abcdefghijklmanopqrstuvwxyz';
        $lettersMayus = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '?_-.,*/;!)(=%#^:]@&[|';
        $chars = $lettersMinus . $lettersMayus . $numbers . $specialChars;
        $sizeLettersMinus = strlen($lettersMinus);
        $sizeLettersMayus = strlen($lettersMayus);
        $sizeNumbers = strlen($numbers);
        $sizeSpecialChars = strlen($specialChars);
        $sizeChars = strlen($chars);
        for ($i = 0; $i < $lengthStep1; $i++) {
            $string .= $chars[rand(0, $sizeChars - 1)];
        }
        for ($i = 0; $i < $lengthStep2; $i++) {
            $string .= $specialChars[rand(0, $sizeSpecialChars - 1)];
        }
        for ($i = 0; $i < $lengthStep3; $i++) {
            $string .= $lettersMayus[rand(0, $sizeLettersMayus - 1)];
        }
        for ($i = 0; $i < $lengthStep4; $i++) {
            $string .= $lettersMinus[rand(0, $sizeLettersMinus - 1)];
        }
        for ($i = 0; $i < $lengthStep5; $i++) {
            $string .= $numbers[rand(0, $sizeNumbers - 1)];
        }
        for ($i = 0; $i < $lengthStep6; $i++) {
            $string .= $specialChars[rand(0, $sizeSpecialChars - 1)];
        }
        for ($i = 0; $i < $lengthStep7; $i++) {
            $string .= $lettersMinus[rand(0, $sizeLettersMinus - 1)];
        }
        for ($i = 0; $i < $lengthStep8; $i++) {
            $string .= $numbers[rand(0, $sizeNumbers - 1)];
        }
        for ($i = 0; $i < $lengthStep9; $i++) {
            $string .= $lettersMayus[rand(0, $sizeLettersMayus - 1)];
        }
        return $string;
    }
}

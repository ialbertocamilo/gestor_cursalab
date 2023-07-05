<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use LangleyFoxall\LaravelNISTPasswordRules\Rules\ContextSpecificWords;

class CustomContextSpecificWords extends ContextSpecificWords
{
    protected $identifier;

    public function __construct($value, $identifier)
    {
        parent::__construct($value);
        $this->identifier = $identifier;
    }

    public function message()
    {
        switch ($this->identifier) {
            case 'document':
                return "No puedes incluir tu Documento de Identidad en la contraseña. Usa otros términos que te sean fácil de recordar.";
            case 'name':
                return "No puedes incluir tu nombre en la contraseña. Usa otros términos que te sean fáciles de recordar.";
            case 'lastname':
                return "No puedes incluir tu nombre ni apellido en la contraseña. Usa otros términos que te sean fácil de recordar.";
            case 'surname':
                return "No puedes incluir tu nombre ni apellido en la contraseña. Usa otros términos que te sean fácil de recordar.";
            default:
                return parent::message();
        }
    }
}

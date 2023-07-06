<?php 

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomContextSpecificWords implements Rule
{
    protected $message;
    protected $words = [];
    protected $detectedWord = null;

    /**
     * ContextSpecificWords constructor.
     */
    public function __construct($username, $identifier)
    {
        $text = '';
        $text = config('app.name');
        $text .= ' ';
        $text .= str_replace(
            ['http://', 'https://', '-', '_', '.com', '.org', '.biz', '.net', '.'],
            ' ',
            config('app.url')
        );
        $text .= ' ';
        $text .= $username;

        $words = explode(' ', strtolower($text));

        foreach ($words as $key => $word) {
            if (strlen($word) < 3) {
                unset($words[$key]);
            }
        }

        $this->words = $words;
        $this->identifier = $identifier;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = strtolower($value);

        foreach ($this->words as $word) {
            if (stripos($value, $word) !== false) {
                $this->detectedWord = $word;

                return $this->fail();
            }
        }

        return true;
    }

    protected function fail() 
    {
    	switch ($this->identifier) {
    		case 'email':
    			$build_message = 'El campo correo electrónico no es un correo válido';
			break;

    		case 'document':
    			$build_message = 'No puedes incluir tu Documento de Identidad \''.$this->detectedWord.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar.";
			break;

    		case 'name':
    			$build_message = 'No puedes incluir tu nombre \''.$this->detectedWord.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar.";
			break;

    		case 'lastname':
    			$build_message = 'No puedes incluir tu nombre ni apellido \''.$this->detectedWord.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar.";
			break;

    		case 'surname':
    			$build_message = 'No puedes incluir tu nombre ni apellido \''.$this->detectedWord.'\' en la contraseña.'."\n Usa otros términos que te sean fácil de recordar.";
			break;

    		default: 
    			$build_message = __('laravel-nist-password-rules::validation.can-not-contain-word', ['word' => $this->detectedWord]);
    		break;
    	}

    	$this->message = $build_message;
        
    	return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
    	return $this->message;
    }
}
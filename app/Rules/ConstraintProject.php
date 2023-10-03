<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ConstraintProject implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $type;
    private $files;
    private $multimedias;
    private $message = '';
    public function __construct($type,$files=[],$multimedias=[])
    {
        $this->type = $type;
        $this->files =  $files;
        $this->multimedias =  $multimedias;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $constraints = config('project.constraints.'.$this->type);
        $count_files = count($this->files);
        $count_multimedias = count($this->multimedias);
        if(($count_files + $count_multimedias) > $constraints['max_quantity_upload_files']){
            $this->message = 'La cantidad máxima de archivos es: '.$constraints['max_quantity_upload_files'];
            return false;
        }
        foreach ($this->multimedias as $multimedia) {
            if(isset($multimedia['size']) && !is_null($multimedia['size']) && $multimedia['size']>$constraints['max_size_upload_files']){
                $this->message = 'Los archivos deben pesar como máximo: '.$constraints['max_size_upload_files'].' MB';
                return false;
            }
        }
        foreach ($this->files as $file) {
            try {
                $size = filesize($file[0]);
                //code...
            } catch (\Throwable $th) {
                $size = filesize($file);
            }
            $file_size =  $size ? number_format($size / 1048576, 2) : 0;
            if($file_size > $constraints['max_size_upload_files']){
                $this->message = 'Los archivos deben pesar como máximo: '.$constraints['max_size_upload_files'].' MB';
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}

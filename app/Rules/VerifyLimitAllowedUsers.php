<?php


namespace App\Rules;

use App\Models\Workspace;
use Illuminate\Contracts\Validation\Rule;

class VerifyLimitAllowedUsers implements Rule
{
    private $method_type;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($method_type)
    {
        $this->method_type = $method_type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (in_array($this->method_type, ['POST', 'PUT'])):

            if ($value):

                $current_workspace = get_current_workspace();

                return $current_workspace->verifyLimitAllowedUsers();

            endif;

        endif;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return config('errors.limit-errors.limit-user-allowed');
    }
}

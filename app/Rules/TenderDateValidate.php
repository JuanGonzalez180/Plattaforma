<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class TenderDateValidate implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // $date = new Carbon($value);
        // $date2 = new Carbon('2021-12-12');

        // return $date->lessThan($date2);
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La fecha final de la licitacion debe ser menor a la fecha final del proyecto.';
    }
}

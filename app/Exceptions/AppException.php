<?php

namespace App\Exceptions;

use Exception;

class AppException extends Exception
{
    /**
     * Redirect back with the exception message shown as an error flash.
     */
    public function render($request)
    {
        return redirect()
            ->back()
            ->with('error', $this->getMessage());
    }
}

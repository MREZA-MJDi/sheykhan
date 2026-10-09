<?php

namespace App\Http\Requests\Owner\Content;

class UpdateContentRequest extends StoreContentRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}

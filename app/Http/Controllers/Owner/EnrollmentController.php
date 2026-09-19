<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class EnrollmentController extends Controller
{
    public function store(): RedirectResponse
    {
        abort(410, 'این endpoint توسط PeopleController مدیریت می‌شود.');
    }
}

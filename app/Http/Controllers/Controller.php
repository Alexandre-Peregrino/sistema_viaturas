<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Usa o alias para evitar conflito de nome

class Controller extends BaseController // Estende a classe base do Laravel
{
    use AuthorizesRequests, ValidatesRequests;
}

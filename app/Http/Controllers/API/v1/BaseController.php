<?php

namespace App\Http\Controllers\API\v1;

use Aimeos\MShop;
use App\Http\Controllers\Controller;


abstract class BaseController extends Controller
{

    protected $manager;
    protected $context;


    public function __construct(MShop $manager)
    {

        $this->manager = $manager;
        $this->context = app('aimeos.context')->get();

    }


}

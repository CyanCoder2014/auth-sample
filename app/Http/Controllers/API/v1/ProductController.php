<?php

namespace App\Http\Controllers\API\v1;


class ProductController extends BaseController
{


    public function getProduct($id)
    {

        $getProduct = $this->manager->create( $this->context, 'product' );
        $product = $getProduct->get($id)->toArray();

        return $product;

    }

}

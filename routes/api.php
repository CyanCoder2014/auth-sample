<?php

use App\Http\Controllers\API\v1\Auth\AuthController;
use App\Http\Controllers\API\v1\Auth\RegisterController;
use App\Http\Controllers\API\v1\CartController;
use App\Http\Controllers\API\v1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use Aimeos\MShop;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

//    Auth::routes(['verify' => true]);

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout',[AuthController::class, 'logout'])->name('logout');
    Route::post('refresh',[AuthController::class, 'refresh'])->name('refresh');
    Route::post('me', [AuthController::class, 'me'])->name('me');
    Route::get('loginfailed', [AuthController::class, 'loginfailed'])->name('loginfailed');


    Route::post('register',[RegisterController::class, 'register'])->name('register');
});


Route::get('getProduct/{id}',[ProductController::class, 'getProduct'])->name('getProduct')
    ->middleware('auth:api')->middleware('verified');

Route::get('getCart',[CartController::class, 'get'])->name('getCart');
//    ->middleware('auth:api')->middleware('verified');




Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories',function(Request $req){
    $context = app('aimeos.context')->get();
    $manager = MShop::create( $context, 'catalog' );
    $categories = $manager->search($manager->filter(),['text','catalog','media', 'default', 'stage']);
    $catsList = [];
    foreach($categories as $cat){


        foreach( $cat->getListItems( 'media', 'default', 'stage' ) as $listItem ) {
            $getMedia = $listItem->getRefItem('media')->toArray(); // media item
            $catImg['url'] = $getMedia['media.url'];
            $catImg['responsive'] =  $getMedia['media.previews'];

        }


        $items = \Aimeos\Shop\Facades\Product::uses(['text', 'media', 'price','name'])
            ->category($cat->id)
            ->sort('name')->slice(0, 3)->search();

        $products = [];
        foreach($items as $product) {
            $productOutput = [];
            $productOutput['title'] = $product['product.label'];
            $productOutput['code'] = $product['product.code'];
            $productOutput['id'] = (int) $product['product.id'];

            $text = $product->getRefItems('text');
            $desc = '';
            $label = '';
            foreach($text as $key=>$txt){
                if($txt['text.type']=='short'){
                    $desc = $txt['text.content'];
                }
                if($txt['text.type']=='name'){
                    $label = $txt['text.content'];
                }
            }
            $productOutput['desc'] = $desc;
            $productOutput['label'] = $label;

            $getPrice = $product->getRefItems('price');
            $priceArr = [];
            if (!empty($getPrice)) {
                foreach ($getPrice as $price) {
                    $priceArr[] = $price->toArray();
                }
            }
            $correctPrice = [];
            foreach ($priceArr as $price) {
                $priceVal['currency'] = $price['price.currencyid'];
                $priceVal['value'] = $price['price.value'];
                $priceVal['tax_value'] = $price['price.taxvalue'];
                $priceVal['tax_rate'] = $price['price.taxrate'];
                $priceVal['rebate'] = $price['price.rebate'];
                $priceVal['total'] = ($price['price.value'] - $price['price.rebate']) + $price['price.costs'] + $price['price.taxvalue'];
            }
            $productOutput['price'] = $priceVal;

            $getMedia = $product->getRefItems('media')->toArray();
            $mediaArr = [];
            if (!empty($getMedia)) {
                foreach ($getMedia as $media) {
                    $mediaArr[] = $media->toArray();
                }
            }

            $prMedia = [];
            foreach ($mediaArr as $md) {

                $val['url'] = $md['media.url'];
                $val['responsive'] = $md['media.previews'];
                $prMedia[] = $val;
            }

            $productOutput['product_image'] = @$prMedia[0]['url'];
            $productOutput['gallery'] = $prMedia;
            $products[] = $productOutput;
        }

        $text = $cat->getRefItems('text');
        $desc = '';
        foreach($text as $key=>$txt){
            if($txt['text.type']=='short'){
                $desc = $txt['text.content'];
            }
        }
        $catsList[] = [
            'id'=>(int)$cat->id,
            'label'=>$cat->label,
            'code'=>$cat->code,
            'media'=>@$catImg,
            'desc'=>$desc,
            'products'=>$products
        ];
    }
    return response()->json(['res'=>$catsList]);
});

Route::get('/product/{id}',function(Request $req,$id){
    $context = app('aimeos.context')->get();
    $manager = MShop::create( $context, 'product' );
    $product = $manager->get($id,['text', 'price', 'media', 'attribute', 'product','catalog']);

    $productOutput = [];
    $productOutput['title'] = $product['product.label'];
    $productOutput['code'] = $product['product.code'];
    $productOutput['id'] = (int) $product['product.id'];
    $getPrice = $product->getRefItems('price');
    $priceArr = [];
    if(!empty($getPrice)){
        foreach($getPrice as $price){
            $priceArr[] = $price->toArray();
        }
    }
    $correctPrice = [];
    foreach($priceArr as $price){
        $val['currency']=$price['price.currencyid'];
        $val['value']=$price['price.value'];
        $val['tax_value']=$price['price.taxvalue'];
        $val['tax_rate']=$price['price.taxrate'];
        $val['rebate']=$price['price.rebate'];
        $val['total']=($price['price.value'] - $price['price.rebate']) + $price['price.costs'] + $price['price.taxvalue'];
    }
    $productOutput['price']=$val;

    $getMedia = $product->getRefItems('media')->toArray();
    $mediaArr = [];
    if(!empty($getMedia)){
        foreach($getMedia as $media){
            $mediaArr[] = $media->toArray();
        }
    }

    $correctMedia = [];
    foreach($mediaArr as $md){

        $val['url'] = $md['media.url'];
        $val['responsive'] = $md['media.previews'];
        $correctMedia[] = $val;
    }


    $cats = $product->getCatalogItems();
    $catsList = [];
    foreach($cats as $cat){
        $catsList[] = ['id'=>(int)$cat->id,'label'=>$cat->label,'code'=>$cat->code];
    }
    $productOutput['category'] = $catsList[0];
    $productOutput['categories'] = $catsList;
    $productOutput['product_image'] = @$correctMedia[0]['url'];
    $productOutput['gallery']=$correctMedia;

    return response()->json(['res'=>$productOutput]);
});

Route::get('/products',function(Request  $req){
    $context = app('aimeos.context')->get();
    $manager = MShop::create( $context, 'product' );
    $products = $manager->search($manager->filter(),['text', 'price', 'media', 'attribute', 'product','catalog']);
    $productsList = [];
    foreach($products as $id=>$pr){
        $cats = $pr->getCatalogItems();

        $catsList = [];
        foreach($cats as $cat){
            $catsList[] = ['id'=>(int)$cat->id,'label'=>$cat->label,'code'=>$cat->code];
        }
        $productsListItem['category'] = @$catsList[0];

        $productsListItem['id'] = (int)$id;
        $getPrice = $pr->getRefItems('price');
        $priceArr = [];
        if(!empty($getPrice)){
            foreach($getPrice as $price){
                $priceArr[] = $price->toArray();
            }
        }
        $correctPrice = [];
        foreach($priceArr as $price){
            $val['currency']=$price['price.currencyid'];
            $val['value']=$price['price.value'];
            $val['tax_value']=$price['price.taxvalue'];
            $val['tax_rate']=$price['price.taxrate'];
            $val['rebate']=$price['price.rebate'];
            $val['total']=($price['price.value'] - $price['price.rebate']) + $price['price.costs'] + $price['price.taxvalue'];
        }
        $productsListItem['price']=$val;

        $getMedia = $pr->getRefItems('media')->toArray();
        $mediaArr = [];
        if(!empty($getMedia)){
            foreach($getMedia as $media){
                $mediaArr[] = $media->toArray();

            }
        }

        $correctMedia = [];
        foreach($mediaArr as $md){

            $val['url'] = $md['media.url'];
            $val['responsive'] = $md['media.previews'];
            $correctMedia[] = $val;
        }

        $productsListItem['product_image'] = @$correctMedia[0]['url'];
        $productsListItem['gallery']=$correctMedia;
        $productsList[] = $productsListItem;

    }

    return response()->json(['data'=>$productsList]);
});
//Route::group('payment',function(){
//    Route::get('/request', [PaymentController::class, 'request']);
//    Route::get('/charge', [PaymentController::class, 'charge']);
//});



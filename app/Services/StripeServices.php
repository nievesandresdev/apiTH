<?php

namespace App\Services;
use Stripe\StripeClient;


class StripeServices {

    protected $stripe;

    public function __construct () {
        $key = config('cashier.secret');
        $this->stripe = new StripeClient($key);
    }

    public function get_products ($price_id = null) {
        $products = $this->stripe->products->all();
        $products = collect($products->data);

        $plans = $this->stripe->plans->all();
        $plans = collect($plans->data);

        $products = $products
            ->filter(function($product){
                $NAME_PRODUCTS_AVAILABLE = ['DigitalHost', 'HostMaster'];
                $available = in_array($product->name, $NAME_PRODUCTS_AVAILABLE);
                return $available && $product->active;
            })
            ->map(function($product)use($plans, $price_id){
                $plans = $plans->where('product', $product->id);
                if ($price_id){
                    $plans = $plans->where('id', $price_id);
                    $plans = $plans->first();
                } else {
                    $plans = $plans->values()->collect();
                }
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'active' => $product->active,
                    'plans' => $plans
                ];
            })
            ->collect();
        if ($price_id){
            $products = $products->filter(function($product){
                return $product['plans'];
            });
            $products = $products->first();
        } else {
            $products = $products->values()->collect();
        }
        return $products;
    }

}

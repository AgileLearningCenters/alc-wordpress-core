<?php

function wpspc_get_total_cart_qty()
{
    $total_items = 0;
    if(!isset($_SESSION['simpleCart'])){
        return $total_items;
    }
    foreach ($_SESSION['simpleCart'] as $item){
        $total_items += $item['quantity'];
    }
    return $total_items;
}

function wpspc_get_total_cart_sub_total()
{
    $sub_total = 0;
    if(!isset($_SESSION['simpleCart'])){
        return $sub_total;
    }    
    foreach ($_SESSION['simpleCart'] as $item){
        $sub_total += $item['price'] * $item['quantity'];
    }
    return $sub_total;
}

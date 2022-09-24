<?php

class ProductService{

	public static function GetProducts(){
		// Start
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		// Retrieve all products from Picqer account
		$products = $apiClient->getAllProducts();
		return $products;
	}
	public static function GetProduct($idproduct){
		// Start
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		// Retrieve all products from Picqer account
		$product = $apiClient->getProduct($idproduct);
		return $product;
	}
	public static function updateProductStockForWarehouse($idproduct,$idwarehouse,$params){
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		// Retrieve all products from Picqer account
		$stockChange = $apiClient->updateProductStockForWarehouse($idproduct,$idwarehouse,$params);
		return $stockChange;
	}


	public static function saveProductToWP(){
		$products = ProductService::GetProducts()['data'];
		foreach ($products as $product){
			$desc = "";
			if($product['description'] != ""){
				$desc = $product['description'];
			}
			wp_insert_post( array(
				'post_title'    => $product['name'],
				'post_content' => $desc,
				'post_type'     => 'products',
				'post_status' => 'publish',
				'meta_input' => array(
					'stocks' => $product['stock'],
					'price' => $product['price'],
					'idproduct' => $product['idproduct'],
					'productcode' => $product['productcode']
				)
			) );
		}
	}
	public static function updateProductStockForWarehouseWP($postID,$idwareshouse,$params){
		$newWarehouse = true;
		$post_stocks = get_post_meta($postID,'stocks')[0];
		for($i = 0;$i<count($post_stocks);$i++){
			if($post_stocks[$i]['idwarehouse'] == $idwareshouse){
				$post_stocks[$i]['stock'] += $params['change'];
				$post_stocks[$i]['freestock'] += $params['change'];
				$newWarehouse = false;
			}
		}
		if($newWarehouse){
			array_push($post_stocks,(array)new Warehouse($idwareshouse,$params['change']));

//			$params = [
//				"amount" => $params['change'],
//				"reason" => "New stock at new warehouse"
//			];
		}
		update_post_meta($postID,'stocks',$post_stocks);
		ProductService::updateProductStockForWarehouse(get_post_meta($postID,'idproduct')[0],$idwareshouse,$params);
	}

	public static function createProduct($params){
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		$product = $apiClient->addProduct($params);
		return $product;
	}
	public static function getVatgroups(){
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		$vatgroups = $apiClient->getVatgroups();
		return $vatgroups;
	}
	public static function DeleteProduct(){

	}
}
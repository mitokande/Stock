<?php


class WarehouseService{

	public static function GetWarehouse($idwarehouse){
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		// Retrieve all products from Picqer account
		$warehouse = $apiClient->getWarehouse($idwarehouse);
		if($warehouse['success']){
			return new ResultData(true,"200",$warehouse['data']);
		}
		return new ResultData($warehouse['success'],$warehouse['errorcode'],null);

	}
	public static function GetWarehouses(){
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		// Retrieve all products from Picqer account
		$warehouses = $apiClient->getWarehouses();
		if($warehouses['success']){
			return new ResultData(true,"200",$warehouses['data']);
		}
		return new ResultData($warehouses['success'],$warehouses['errorcode'],null);

	}
	public static function GetWarehousesWP(){
		$warehouses = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'warehouses'
		));
		if(count($warehouses) == 0){
			return new ResultData(false,404,"No warehouse found");
		}
		return new ResultData(true,"200",$warehouses);
	}
	public static function GetWarehouseWP($idwarehouse){
		$warehouse = get_posts(array(
			'numberposts' => 1,
			'post_type' => 'warehouses',
			'meta_query' => array(
				array(
					'key' => 'idwarehouse',
					'value' => $idwarehouse,
					'compare' => '='
				)
			)
		));
		return new ResultData(true,"200",$warehouse);
	}
	public static function GetProductStockForWarehouse($idproduct, $idwarehouse)
	{
		$apiClient = new Picqer\Api\Client(get_option('picqer_subdomain'), get_option('picqer_api'));
		$apiClient->enableRetryOnRateLimitHit();
		$apiClient->setUseragent('Wordpress test plugin');

		// Retrieve all products from Picqer account
		$warehouseStock = $apiClient->getProductStockForWarehouse($idproduct,$idwarehouse);
		return $warehouseStock;
	}
	public static function GetProductsWithWarehouse($idwarehouse){
		$products = ProductService::GetProducts();
		$productsWithWarehouse = [];
		foreach ($products['data'] as $product){
			foreach ($product['stock'] as $warehouse){
				if($warehouse['idwarehouse'] == $idwarehouse){
					$productsWithWarehouse[] = $product;
				}
			}
		}
		return $productsWithWarehouse;
	}

	public static function saveWarehousesToWP(){
		$result = WarehouseService::GetWarehouses();
		if($result->success){
			$warehouses = $result->data;
			foreach ($warehouses as $warehouse){
				$name = WarehouseService::GetWarehouse($warehouse['idwarehouse'])->data;
				wp_insert_post( array(
					'post_title'    => $name['name'],
					'post_type'     => 'warehouses',
					'post_status' => 'publish',
					'meta_input' => array(
						'idwarehouse' => $warehouse['idwarehouse']
					)
				) );
			}
		}
	}
}
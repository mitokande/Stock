<?php



class WarehouseMenuController{

	public static function registerSubMenuPage(){
		add_submenu_page("picqer","Warehouses","Warehouses","manage_options","picqer_warehouses",array(WarehouseMenuController::class,'picqer_warehouse_dashboard_html'));
	}

	public static function picqer_warehouse_dashboard_html(){
		$warehouses = WarehouseService::GetWarehouses();
		//print_r($warehouses);
		if($warehouses->success){
			echo '<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col">idwarehouse</th>
				<th scope="col">Warehouse Name</th>
				<th scope="col">Stocks</th>
				<th scope="col">Activeness</th>
			</tr>
			</thead>
			<tbody>';
			foreach ($warehouses->data as $warehouse){
				echo '	<tr><td>' . $warehouse['idwarehouse'] . '</td>
					<td>' . $warehouse['name'] . '</td>
					<td>';
				$warehouseStock = WarehouseService::GetProductsWithWarehouse($warehouse['idwarehouse']);
				//print_r($warehouseStock);
				foreach($warehouseStock as $productInWarehouse){
					$productStockInWarehouse = WarehouseService::GetProductStockForWarehouse($productInWarehouse['idproduct'],$warehouse['idwarehouse']);
					echo $productInWarehouse['name']. " => " . $productStockInWarehouse['data']['stock'] . "<br>";
				}

				echo    '</td>
					<td>' . $warehouse['active'] . '</td></tr>';
			}
		}
		else{
			echo 'Wrong Picqer Api Configuration';
		}
	}


}
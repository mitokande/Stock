<?php


class ProductMenuController{

	public static function registerProductMenu(){
		add_menu_page("Picqer","Picqer","manage_options","picqer",array(ProductMenuController::class,'picqer_dashboard_page_html'),"https://i.hizliresim.com/gao86bz.png");
		wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css' );
		wp_enqueue_style( 'st', 'http://localhost/wp1/wp-content/plugins/stock/prodcutStyle.css' );

	}


	public static function picqer_dashboard_page_html(){
		$products = ProductService::GetProducts();
		echo '<div class="wrap"><h1>My Products</h1>';
		echo "<br>";
		echo '<table class="table table-striped">
			<thead>
			<tr>
				<th scope="col">idproduct</th>
				<th scope="col">Product Name</th>
				<th scope="col">Product Code</th>
				<th scope="col">Description</th>
				<th scope="col">Stocks</th>
				<th scope="col">Total Product Stock</th>
			</tr>
			</thead>
			<tbody>';
		foreach ($products['data'] as $product){
			echo '
			<tr>
				<th scope="row">'.$product["idproduct"].'</th>
				<td>'.$product["name"].'</td>
				<td>'.$product["productcode"].'</td>
				<td>'.$product["description"].'</td>
				<td>';
			$stockCount = 0;
			foreach ($product['stock'] as $productWarehouse){
				$stockCount += $productWarehouse['freestock'];
				$warehouse = WarehouseService::GetWarehouse($productWarehouse['idwarehouse']);
				echo $warehouse->data['name']. " => " . $productWarehouse['freestock'] . "<br>";

			}
			echo'</td>
				<td>' . $stockCount . '</td>
			</tr>';
		}
		echo '</tbody>
		</table></div>';

	}



}
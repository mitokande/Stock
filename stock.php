<?php
/**
 * @package Stock
 */
/*
 * Plugin Name: Picqer Product Stock Management
 * Description: Wordpress Plugin for Picqer Stock Management Api
 * Version: 1.0
 * Author: Mithat Can Turan
 */
require 'src/Services/ProductService.php';
require 'src/Services/WarehouseService.php';
require 'PicqerPackage/Client.php';
require 'src/Controllers/ProductMenuController.php';
require 'src/Controllers/WarehouseMenuController.php';
require 'src/Entities/ResultData.php';
require 'src/Entities/Warehouse.php';
class Picqer{
	public function __construct() {
		add_action("admin_menu",array($this,'adminPage'));
		add_action("admin_init",array($this,'settings'));
        add_action("admin_menu",array($this,'registerMenus'));
        add_action("init",array($this,'registerPicqerTypes'));
		add_filter('template_include', function($template) {
			if (is_page('Products') || is_singular(["products"])) {
				return plugin_dir_path(__FILE__) . 'src/views/product-template.php';
			}
			return $template;
		}, 99);
        add_action("add_meta_boxes",array($this,'add_metas'));
		add_action('save_post',array( $this, 'save') );


		add_action("picqer_init_data",array(ProductService::class,'saveProductToWP'));
		add_action("picqer_init_data",array(WarehouseService::class,'saveWarehousesToWP'));
	}
    function registerMenus(){
	    ProductMenuController::registerProductMenu();
        WarehouseMenuController::registerSubMenuPage();
    }
	function settings(){
		add_settings_section("picqer_settings_first_section",null,null,"picqer-settings-page");

		add_settings_field("picqer_api","picqer Api Key",array($this,'apiKeyHtml'),"picqer-settings-page","picqer_settings_first_section");
		register_setting("picqer_settings","picqer_api",array('sanitize_callback'=>'sanitize_text_field','default'=>'YOUR_API_KEY'));

		add_settings_field("picqer_subdomain","picqer Subdomain",array($this,'subdomainHtml'),"picqer-settings-page","picqer_settings_first_section");
		register_setting("picqer_settings","picqer_subdomain",array('sanitize_callback'=>'sanitize_text_field','default'=>'YOUR_SUBDOMAIN'));

	}
	function apiKeyHtml(){
		?>
		<input name="picqer_api" type="text" value="<?php echo get_option('picqer_api'); ?>">
		<?php
	}
    function subdomainHtml(){
        ?>
        <input name="picqer_subdomain" type="text" value="<?php echo get_option('picqer_subdomain') ?>">
        <?php
    }
	function adminPage(){
		add_options_page("picqer Settings","picqer Settings","manage_options","picqer-settings-page",array($this,'settingHtml'));
	}

	function settingHtml(){
		?>
		<div class="wrap">
			<h1>picqer Api Settings</h1>
			<form action="options.php" method="post">
				<?php
				settings_fields("picqer_settings");
				do_settings_sections("picqer-settings-page");
				submit_button();

				?>
			</form>
		</div>
        <div class="wrap">
            <form method="post">
                <input type="submit" value="Fetch" name="prod">
            </form>
        </div>
		<?php

        if(isset($_POST['prod'])){
	        do_action( 'picqer_init_data' );
        }
	}


    function registerPicqerTypes(){
	    register_post_type( 'products',
		    // CPT Options
		    array(
			    'labels' => array(
				    'name' => __( 'Products' ),
				    'singular_name' => __( 'Product' )
			    ),
			    'public' => true,
			    'has_archive' => true,
			    'rewrite' => array('slug' => 'products'),
			    'show_in_rest' => true,
		    )
	    );
	    register_post_type( 'warehouses',
		    // CPT Options
		    array(
			    'labels' => array(
				    'name' => __( 'Warehouses' ),
				    'singular_name' => __( 'Warehouse' )
			    ),
			    'public' => true,
			    'has_archive' => true,
			    'rewrite' => array('slug' => 'warehouse'),
			    'show_in_rest' => true,
		    )
	    );


    }
    function add_metas(){
	    add_meta_box("stocks","Stocks/Warehouses",array($this,'warehouse_meta_html'),"products","normal","default");

    }

    function warehouse_meta_html($post){

	    $stockArr = get_post_meta($post->ID,"stocks");
        if(empty($stockArr)){
            //stocks meta is empty only when creating a post from wp admin

            add_post_meta($post->ID,'stocks',[]);
        }
        $productWarehouses = [];
        echo '<div><label>Product Price</label><input name="price" value="'.get_post_meta($post->ID,'price')[0].'" type="text"></div>';
        foreach($stockArr[0] as $warehouse){



	        $warehouseData = WarehouseService::GetWarehouseWP($warehouse['idwarehouse'])->data;

            echo '
<div class="wrap">
                    <p style="font-size: 18px">Warehouse:'. $warehouseData[0]->post_title .'</p>
                    <p style="font-size: 18px">FreeStock:'. $warehouse["freestock"] .'</p><hr>
            </div>';
        }
	    $productWarehouses = WarehouseService::GetWarehousesWP()->data;

	    echo '<p style="font-size: 16px">Save Post to update stock</p><label>Select Warehouse</label><select name="warehouse">';
              foreach ($productWarehouses as $product_warehouse){
                  echo '<option value="'.get_post_meta($product_warehouse->ID,'idwarehouse')[0].'">'.$product_warehouse->post_title.'</option>';
              }

        echo '</select>
        <label>Amount:</label><input type="text" name="quantity" value="0">
        ';

    }
    function save($post_id){
	       if(isset($_POST['warehouse'])) {
		       if($_POST['quantity'] > 0){
			       $amount      = $_POST['quantity'];
			       $warehouseID = $_POST['warehouse'];


			       $params = [
				       "change" => $amount,
				       "reason" => "New Stock Added From Wp Edit Page"
			       ];
			       ProductService::updateProductStockForWarehouseWP( $post_id, $warehouseID, $params );

		       }
		       if ( get_post_meta( $post_id, 'productcode' )[0] == null ) {
			       $params = array(
				       "idvatgroup"  => ProductService::getVatgroups()['data'][0]['idvatgroup'],
				       "productcode" => $post_id . "0000",
				       "name"        => get_post( $post_id )->post_title,
				       "price"       => $_POST['price']
			       );
			       $result = ProductService::createProduct( $params );
			       $idprod = $result['data']['idproduct'];
			       add_post_meta( $post_id, 'idproduct', $idprod );
			       $stockParams = [
				       "amount" => $_POST['quantity'],
				       "reason" => "New Stock added to new product"
			       ];
			       //ProductService::updateProductStockForWarehouseWP( $post_id, $_POST['warehouse'], $stockParams );
			       add_post_meta( $post_id, 'test', ProductService::updateProductStockForWarehouseWP( $post_id, $_POST['warehouse'], $stockParams ));

			       add_post_meta( $post_id, 'price', $_POST['price'] );
		       }
	       }
    }
}


$picqer = new Picqer();
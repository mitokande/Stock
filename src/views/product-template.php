<?php

//    get_header();
$idproduct = get_post_meta(get_the_ID(),"idproduct")[0];
if(isset($_GET['id'])){
	$idproduct = $_GET['id'];
}

$product = ProductService::GetProduct($idproduct)['data'];
//print_r($product);

if(isset($_POST['warehouse'])){
    $params = [
            "change" => -$_POST['quantity'],
            "reason" => "Product Sold"
    ];
    ProductService::updateProductStockForWarehouseWP(get_the_ID(),$_POST['warehouse'],$params);

}
?>
<link rel="stylesheet" href="http://localhost/wp1/wp-content/plugins/stock/prodcutStyle.css">
<div>
    <h1>Picqer Api </h1>
</div>
<main>
    <div class="container">
        <div class="grid second-nav">
            <div class="column-xs-12">
                <nav>
                    <ol class="breadcrumb-list">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Household Plants</a></li>
                        <li class="breadcrumb-item active">Bonsai</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="grid product">
            <div class="column-xs-12 column-md-7">
                <div class="product-gallery">
                    <div class="product-image">
                        <img class="active" src="https://picsum.photos/400">
                    </div>
                    <ul class="image-list">
                        <li class="image-item"><img src="https://picsum.photos/200"></li>
                        <li class="image-item"><img src="https://picsum.photos/200"></li>
                        <li class="image-item"><img src="https://picsum.photos/200"></li>
                    </ul>
                </div>
            </div>
            <div class="column-xs-12 column-md-5">
                <h1><?php echo $product['name']; ?></h1>
                <h2>$<?php echo $product['price']; ?></h2>
                <div class="description">
                    <p>This here is Product Description</p>

                    <p><?php echo $product['description']; ?></p>
                </div>
                <form method="post">
                    <label>Count:</label><input type="number" name="quantity" value="0">
                <div id="ck-button">
                <?php
                foreach ($product['stock'] as $warehouse){
                    $warehouseData = WarehouseService::GetWarehouse($warehouse["idwarehouse"])->data;

                    echo '
                            <div class="button"><label>
                                <input type="radio" name="warehouse" value="'.$warehouse["idwarehouse"].'"><span>'.$warehouseData["name"].' ('.$warehouse["freestock"].')</span>
                            </label></div>
                          ';
                }
                ?></div>
                <br><button type="submit" class="add-to-cart">Buy From Warehouse</button>
                </form>
            </div>
        </div>

    </div>
</main>

<?php get_template_part( 'template-parts/footer-menus-widgets' ); ?>

<?php get_footer(); ?>

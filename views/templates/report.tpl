<html>
<head>
    <title>Weekly Product Report</title>
</head>
<body>
    <h1>Weekly Product Report</h1>
    {foreach $products as $product}
        <div class="product">
            <h2>Product ID: {$product.id_product}</h2>
            <img src="{$product.cover_url}" alt="Product Image">
            <p>URL: <a href="{$product.url}">{$product.url}</a></p>
            <p>Price: {$product.price}</p>
            {if $product.has_discount}
                <p>Regular Price: <span class="regular-price">{$product.regular_price}</span></p>
                {if $product.discount_type === 'percentage'}
                    <p>Discount: <span class="discount-percentage">{$product.discount_percentage}</span></p>
                {/if}
            {/if}
            <p>Reference: {$product.reference|escape:'htmlall':'UTF-8'}</p>
        </div>
    {/foreach}
</body>
</html>

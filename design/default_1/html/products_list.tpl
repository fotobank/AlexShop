
	{foreach $products as $product}
	<!-- Товар-->
	<li class="product">

		<!-- Фото товара -->
		{if $product->image}
		<div class="image">
			<a href="products/{$product->url}"><img src="{$product->image->filename|resize:200:200}" alt="{$product->name|escape}"/></a>
		</div>
		{/if}
		<!-- Фото товара (The End) -->

		<div class="product_info">
		<!-- Название товара -->
		<h3 class="{if $product->featured}featured{/if}"><a data-product="{$product->id}" href="products/{$product->url}">{$product->name|escape}</a></h3>
		<!-- Название товара (The End) -->

        <br style="clear: both">
        {if $compare->ids && in_array($product->id, $compare->ids)}
        <span class='mylist_add'>уже в списке</span>
        {else}
        <span class='mylist_add'><a href="/compare?id={$product->id}" class='addps' data-id='{$product->id}' data-key='compare' data-informer='1' data-result-text='уже в списке'>добавить к сравнению</a></span>
        {/if}

        {if $page->url !== 'wishlist'}
            {if $wishlist->ids && in_array($product->id, $wishlist->ids)}
            <span class='mylist_add'>уже в списке</span>
            {else}
            <span class='mylist_add'><a href="/wishlist?id={$product->id}" class='addps' data-id='{$product->id}' data-key='wishlist' data-informer='1' data-result-text='уже в списке'>добавить в избранное</a></span>
            {/if}
        {else}
            <span class='mylist_add'><a href="/wishlist?remove={$product->id}">удалить</a></span>
        {/if}
        <br><br style="clear: both">

		<!-- Описание товара -->
		<div class="annotation">{$product->annotation}</div>
		<!-- Описание товара (The End) -->

		{if $product->variants|count > 0}
		<!-- Выбор варианта товара -->
		<form class="variants" action="/cart">
			<table>
			{foreach $product->variants as $v}
			<tr class="variant">
				<td>
					<input id="variants_{$v->id}" name="variant" value="{$v->id}" type="radio" class="variant_radiobutton" {if $v@first}checked{/if} {if $product->variants|count<2}style="display:none;"{/if}/>
				</td>
				<td>
					{if $v->name}<label class="variant_name" for="variants_{$v->id}">{$v->name}</label>{/if}
				</td>
				<td>
					{if $v->compare_price > 0}<span class="compare_price">{$v->compare_price|convert}</span>{/if}
					<span class="price">{$v->price|convert} <span class="currency">{$currency->sign|escape}</span></span>
				</td>
			</tr>
			{/foreach}
			</table>
			<input type="submit" class="button" value="в корзину" data-result-text="добавлено"/>
		</form>
		<!-- Выбор варианта товара (The End) -->
		{else}
			Нет в наличии
		{/if}

		</div>

	</li>
	<!-- Товар (The End)-->
	{/foreach}

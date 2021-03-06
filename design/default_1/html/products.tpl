{* Список товаров *}

{* Канонический адрес страницы *}
{if $category && $brand}
    {$canonical="/catalog/{$category->url}/{$brand->url}" scope=parent}
{elseif $category}
    {$canonical="/catalog/{$category->url}" scope=parent}
{elseif $brand}
    {$canonical="/brands/{$brand->url}" scope=parent}
{elseif $keyword}
    {$canonical="/products?keyword={$keyword|escape}" scope=parent}
{else}
    {$canonical="/products" scope=parent}
{/if}

<!-- Хлебные крошки /-->
<div id = "path">
	<a href = "/">Главная</a>
    {if $category}
        {foreach $category->path as $cat}
            →
            <a href = "catalog/{$cat->url}">{$cat->name|escape}</a>
        {/foreach}
	{if $brand}
        →
        <a href = "catalog/{$cat->url}/{$brand->url}">{$brand->name|escape}</a>
    {/if}
	{elseif $brand}
	→
        <a href = "brands/{$brand->url}">{$brand->name|escape}</a>
    {elseif $keyword}
        → Поиск
    {/if}
</div>
<!-- Хлебные крошки #End /-->

{* Заголовок страницы *}
{if $keyword}
    <h1>Поиск {$keyword|escape}</h1>
{elseif $page}
    <h1>{$page->name|escape}</h1>
{else}
    <h1>{$category->name|escape} {$brand->name|escape}</h1>
{/if}


{* Описание страницы (если задана) *}
{$page->body}

{if $current_page_num==1}
    {* Описание категории *}
    {$category->description}
{/if}

{* Фильтр по брендам *}
{if $category->brands}
    <div id = "brands">
	<a href = "catalog/{$category->url}" {if !$brand->id}class = "selected"{/if}>Все бренды</a>
        {foreach $category->brands as $b}
            {if $b->image}
                <a data-brand = "{$b->id}" href = "catalog/{$category->url}/{$b->url}"><img
                            src = "{$config->brands_images_dir}{$b->image}" alt = "{$b->name|escape}"></a>

                            {else}

                <a data-brand = "{$b->id}" href = "catalog/{$category->url}/{$b->url}"
                   {if $b->id == $brand->id}class = "selected"{/if}>{$b->name|escape}</a>
            {/if}
        {/foreach}
</div>
{/if}

{if $current_page_num==1}
    {* Описание бренда *}
    {$brand->description}
{/if}

{* Фильтр по свойствам *}
{if $features}
    <table id = "features">
	{foreach $features as $key=>$f}
        <tr>
	<td class = "feature_name" data-feature = "{$f->id}">
		{$f->name}:
	</td>
	<td class = "feature_values">
		<a href = "{url params=[$f->id=>null, page=>null]}" {if !$smarty.get.$key}class = "selected"{/if}>Все</a>
        {foreach $f->options as $name}
            <a href = "{url params=[$f->id=>$name, page=>null]}" {if $smarty.get.$key == $name}class = "selected"{/if}>{$name|escape}</a>
        {/foreach}
	</td>
	</tr>
    {/foreach}
</table>
{/if}


<!--Каталог товаров-->
{if $products}

    {* Сортировка *}
    {if $products|count>0}
        <div class = "sort">
	Сортировать по
	<a {if $sort=='position'} class = "selected"{/if} href = "{url sort=position page=null}">умолчанию</a>
	<a {if $sort=='price'}    class = "selected"{/if} href = "{url sort=price page=null}">цене</a>
	<a {if $sort=='name'}     class = "selected"{/if} href = "{url sort=name page=null}">названию</a>
</div>
    {/if}


    {include file='pagination.tpl'}

    {get_session_products key=compare}
    {get_session_products key=wishlist}

    <!-- Список товаров-->
    <ul class="products">
    {include file='products_list.tpl'}
    </ul>

    {include file='pagination.tpl'}
    <!-- Список товаров (The End)-->

{else}
    Товары не найдены
{/if}
<!--Каталог товаров (The End)-->
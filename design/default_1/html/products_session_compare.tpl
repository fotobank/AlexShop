{if $smarty.get.remove}
    {remove_product_session key=compare remove=$smarty.get.remove}
{/if}
{if $smarty.get.id}
    {add_product_session key=compare id=$smarty.get.id}
{/if}

{get_products var=products get_session_products=compare category_id=$smarty.get.category data_features=1 data_categories=1}

{if $products|count>0}
{foreach $products as $product}
    {foreach $product->options as $o}
        {$compare_features[{$o->feature_id}] = ['id'=>{$o->feature_id},'name'=>{$o->name}]}
        {$compare_products[{$o->product_id}][{$o->feature_id}] = {$o->value}}
        {$products_categories[$product->category->id] = $product->category}
    {/foreach}
{/foreach}

{if $products_categories}
<div id="brands">
	<a href="/compare" {if !$smarty.get.category}class="selected"{/if}>Все категории</a>
	{foreach $products_categories as $c}
	<a href="{url params=[category=>$c->id]}" {if $c->id == $smarty.get.category}class="selected"{/if}>{$c->name|escape}</a>
	{/foreach}
</div>
{/if}

{literal}
<STYLE type="text/css">
#compare_wrap{max-width:800px;max-height:600px;overflow:auto}
#compare{background: #FFFFFF}
#compare th, #compare td{border:1px solid #DEDEDE;padding:10px;width:200px;min-width:200px;}
#compare tr.odd td{background: #FAFAFA}
</STYLE>
<SCRIPT>
$(function() {
$('a.get_compare').fancybox({'href' : '#compare'});
    $("#compare tr:odd").addClass('odd');
    $('.this_hide').click(function(){
      var idx =$(this).parent().index();
      $('#compare tr').each(function(index) {
        $(this).find('td').eq(idx).hide();
      });
      return false;
    })
});
</SCRIPT>
{/literal}

<div id='compare_wrap'>
<TABLE id='compare'>
    <TR>
        <td>Характеристика</td>
        {foreach $products as $p}
        <td>
            <a href="products/{$p->url}"><img src="{$p->image->filename|resize:100:100}" alt="{$p->name|escape}"/></a>
            <H3>{$p->name}</H3>
            <A href="#" class='this_hide'>скрыть</A>
            {$category=$smarty.get.category}
            {if $products|count==1}{$category=null}{/if}
            <A href="{url params=[category=>$category, id=>null, remove=>$p->id]}" >удалить</A>
        </td>
        {/foreach}
    </TR>
    {foreach $compare_features as $f}
    <TR>
        <TD>{$f.name}</TD>
        {foreach $products as $p}
        <TD>{if {$compare_products.{$p->id}.{$f.id}}}{$compare_products.{$p->id}.{$f.id}}{else}-{/if}</TD>
        {/foreach}
    </TR>
    {/foreach}
</TABLE>
</div>
{else}
    Список пуст
{/if}
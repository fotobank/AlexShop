{* Шаблон текстовой страницы *}

{* Канонический адрес страницы *}
{$canonical="/{$page->url}" scope=parent}

<!-- Заголовок страницы -->
<h1 data-page="{$page->id}">{$page->header|escape}</h1>

<!-- Тело страницы -->
{$page->body}

{if $page->url == 'compare'}
    {include file='products_session_compare.tpl'}
{/if}

{if $page->url == 'wishlist'}
    {include file='products_session_wishlist.tpl'}
{/if}
{if $session->count>0}
	В <a href="/wishlist">избранном</a> {$session->count} {$session->count|plural:'товар':'товаров':'товара'}
{else}
	Список пуст
{/if}
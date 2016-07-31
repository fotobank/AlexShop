{if $session->count>0}
	В <a href="/compare">сравнении</a> {$session->count} {$session->count|plural:'товар':'товаров':'товара'}
{else}
	Список сравнения пуст
{/if}
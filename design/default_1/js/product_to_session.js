
$('.addps').live('click',function(e)
{
	e.preventDefault();
    href     = $(this);
    key      = $(this).data('key');
    id       = $(this).data('id');
    informer = $(this).data('informer');

	$.ajax({
		url: "ajax/product_to_session.php",
		data: {key: key, id: id, i:informer},
		dataType: 'json',
		success: function(data)
        {
            $('#'+key+'_informer').html(data.informer);
			if(href.data('result-text'))
				href.parent().html(href.data('result-text'));
		}
	});
	return false;
});
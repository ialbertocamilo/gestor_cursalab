<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-TBVL8PVKBD"></script>
<script>

var host = window.location.hostname;

if( host == "gestiona.inretail.cursalab.io" )
{
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	@if( auth()->check() )

  	gtag('config', 'G-TBVL8PVKBD', {
  		'user_id': "{{ auth()->user()->id }}"
	});

	@else

	gtag('config', 'G-TBVL8PVKBD');

	@endif
}

</script>

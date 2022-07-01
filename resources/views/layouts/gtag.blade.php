<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-VXJJDN1725"></script>
<script>

var host = window.location.hostname;
// console.log(host)

if( host == "gestor.universidadcorporativafp.com.pe" )
{
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	@if( auth()->check() )

  	gtag('config', 'G-VXJJDN1725', {
  		'user_id': "{{ auth()->user()->id }}"
	});

	@else

	gtag('config', 'G-VXJJDN1725');

	@endif
}

</script>

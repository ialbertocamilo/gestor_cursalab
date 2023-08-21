{{-- <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="{{config('app.gestor')}}"></script>
<script>

var host = window.location.hostname;

// console.log('document.location.pathname')
// console.log(document.location.pathname)

// console.log('document.location')
// console.log(document.location)

if( host == "gestiona.inretail.cursalab.io"  || host =='gestiona.demo.cursalab.io')
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

</script> --}}

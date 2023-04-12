
{{-- Pegue también este código inmediatamente después de la etiqueta <body> de apertura: --}}
	
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M7JT8H2"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script>

var host = window.location.hostname;

if( host == "gestiona.inretail.cursalab.io" )
{
	@if( auth()->check() )

		console.log('authenticated datalayer')
		
		window.dataLayer = window.dataLayer || [];

		window.dataLayer.push({
		   'userId' : "{{ auth()->user()->id }}",
		   'event': 'authenticated',
		})

	@endif
}

</script>
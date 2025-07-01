<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-F0SBQB4FV5"></script>
<script>
	window.dataLayer = window.dataLayer || [];

	function gtag() {
		dataLayer.push(arguments);
	}

	gtag('consent', 'default', {
		ad_user_data: 'denied',
		ad_personalization: 'denied',
		ad_storage: 'denied',
		analytics_storage: 'denied',
		wait_for_update: 500,
	});

	gtag('js', new Date());

	gtag('config', 'G-F0SBQB4FV5');

	localStorage.getItem('cookies_consent') === 'true'
	&& gtag('consent', 'update', {
		ad_user_data: 'granted',
		ad_personalization: 'granted',
		ad_storage: 'granted',
		analytics_storage: 'granted'
	});
</script>

<!-- Meta Pixel Code -->
<script>
	! function(f, b, e, v, n, t, s) {
		if (f.fbq) return;
		n = f.fbq = function() {
			n.callMethod ?
				n.callMethod.apply(n, arguments) : n.queue.push(arguments)
		};
		if (!f._fbq) f._fbq = n;
		n.push = n;
		n.loaded = !0;
		n.version = '2.0';
		n.queue = [];
		t = b.createElement(e);
		t.async = !0;
		t.src = v;
		s = b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t, s)
	}(window, document, 'script',
		'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '1139949147590810');
	fbq('track', 'PageView');
</script>
<noscript><img height="1" src="https://www.facebook.com/tr?id=1139949147590810&ev=PageView&noscript=1" style="display:none" width="1" /></noscript>
<!-- End Meta Pixel Code -->
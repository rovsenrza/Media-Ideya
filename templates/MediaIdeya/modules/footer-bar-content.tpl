<div class="mi-footer__bar">
	<div class="mi-footer__brand mi-reveal mi-reveal--delay" aria-hidden="true">MEDIA IDEYA</div>

	<div class="mi-footer__inner">
		<div class="mi-footer__mobile-head">
			<a class="mi-footer__mobile-logo" href="{THEME}/../../" aria-label="Media Ideya">
				<img src="{THEME}/images/footer/logo-mobile.png" alt="Media Ideya" width="39" height="26" loading="lazy" decoding="async">
			</a>
			<nav class="mi-footer__mobile-nav" aria-label="Навигация в подвале">
				{include file="modules/footer-menu.tpl"}
			</nav>
		</div>

		<div class="mi-footer__contacts">
			{custom category="8" template="modules/settings-contacts" limit="1" order="date" sort="asc" cache="no"}
		</div>

		{custom category="8" template="modules/settings-social" limit="1" order="date" sort="asc" cache="no"}
	</div>

	<div class="mi-footer__legal">
		{custom category="8" template="modules/settings-legal" limit="1" order="date" sort="asc" cache="no"}
		<a href="{THEME}/../../politika-privatnosti.html">Политика приватности</a>
	</div>
</div>

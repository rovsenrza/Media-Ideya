<header class="mi-header" id="header" data-aos="fade-up">
	<div class="mi-header__inner">
		<a class="mi-logo" href="{THEME}/../../" aria-label="Media Ideya">
			<picture>
				<source srcset="{THEME}/images/media-ideya-logo.png" type="image/webp">
				<img src="{THEME}/images/media-ideya-logo.png" alt="Media Ideya" width="89" height="60" decoding="async">
			</picture>
		</a>

		<nav class="mi-nav" aria-label="Main">
			{include file="modules/topmenu.tpl"}
		</nav>

		<button class="mi-btn mi-btn--header" type="button" data-request-modal-open>Оставить заявку</button>
		<button class="mi-menu-toggle" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="mi-mobile-menu" data-mobile-menu-toggle>
			<img src="{THEME}/images/icons/menu-deep.svg" alt="" width="26" height="26" decoding="async">
		</button>
	</div>
	<nav class="mi-mobile-menu" id="mi-mobile-menu" aria-label="Мобильное меню" data-mobile-menu>
		{include file="modules/topmenu.tpl"}
	</nav>
</header>

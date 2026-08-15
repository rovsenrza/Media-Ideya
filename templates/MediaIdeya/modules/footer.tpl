<footer class="mi-footer" id="footer" data-aos="footer">
	<div class="mi-footer__cta">
		<picture class="mi-footer__hand mi-footer__hand--l">
			<source srcset="{THEME}/images/left-hand.webp" type="image/webp">
			<img src="{THEME}/images/left-hand.png" alt="" width="487" height="626" loading="lazy" decoding="async">
		</picture>
		<picture class="mi-footer__hand mi-footer__hand--r">
			<source srcset="{THEME}/images/right-hand.webp" type="image/webp">
			<img src="{THEME}/images/right-hand.png" alt="" width="489" height="626" loading="lazy" decoding="async">
		</picture>

		<div class="mi-footer__cta-inner">
			{custom category="8" template="modules/settings-footer" limit="1" order="date" sort="asc" cache="no"}
			<a class="mi-btn mi-footer__cta-btn" href="{THEME}/../../index.php?do=feedback">Оставить заявку</a>
		</div>
	</div>

	{include file="modules/footer-bar-content.tpl"}
</footer>

<section class="mi-about" id="about" aria-label="О компании" data-aos="about">
	<div class="mi-about__bg" aria-hidden="true">
		<picture class="mi-about__cloud mi-about__cloud--a">
			<source srcset="{THEME}/images/about/cloud.webp" type="image/webp">
			<img src="{THEME}/images/about/cloud.png" alt="" width="1635" height="666" decoding="async">
		</picture>
		<picture class="mi-about__cloud mi-about__cloud--b">
			<source srcset="{THEME}/images/about/cloud.webp" type="image/webp">
			<img src="{THEME}/images/about/cloud.png" alt="" width="2296" height="936" decoding="async">
		</picture>
		<picture class="mi-about__cloud mi-about__cloud--c">
			<source srcset="{THEME}/images/about/cloud.webp" type="image/webp">
			<img src="{THEME}/images/about/cloud.png" alt="" width="1635" height="666" decoding="async">
		</picture>
		<div class="mi-about__colonnade" aria-hidden="true">
			<picture class="mi-about__colonnade-fill">
				<source srcset="{THEME}/images/about/colonnade-fill.webp 1x, {THEME}/images/about/colonnade-fill@2x.webp 2x" type="image/webp">
				<img src="{THEME}/images/about/colonnade-fill.png" srcset="{THEME}/images/about/colonnade-fill.png 1x, {THEME}/images/about/colonnade-fill@2x.png 2x" alt="" width="2342" height="1654" loading="lazy" decoding="async">
			</picture>
			<div class="mi-about__colonnade-glow">
				<img src="{THEME}/images/about/glow.svg" alt="" width="1442" height="659" decoding="async">
			</div>
		</div>
	</div>

	<div class="mi-about__inner">
		<div class="mi-about__text">
			<h2 class="mi-about__title mi-reveal">MEDIA IDEYA</h2>
			{custom category="6" template="modules/about-text" limit="1" order="date" sort="asc" cache="no"}
		</div>

		<div class="mi-about__stats">
			{custom category="9" template="modules/about-stat" limit="3" order="date" sort="asc" cache="no"}
		</div>

		<a class="mi-btn mi-about__cta" href="/o-kompanii.html">Подробнее о компании</a>
	</div>

	<div class="mi-about__wave" aria-hidden="true">
		<img src="{THEME}/images/about/wave.svg" alt="" width="1920" height="229" decoding="async">
	</div>
</section>

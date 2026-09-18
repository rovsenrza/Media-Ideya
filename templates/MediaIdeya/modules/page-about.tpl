<div class="mi-about-page" data-about-page>
	<section class="mi-about-hero" aria-labelledby="mi-about-hero-title" data-about-hero>
		<div class="mi-about-hero__canvas">
			<div class="mi-about-hero__world" data-about-hero-world aria-hidden="true">
				<img class="mi-about-hero__cloud mi-about-hero__cloud--left" src="{THEME}/images/pages/about/cloud.webp" width="4096" height="1668" alt="" decoding="async" fetchpriority="high">
				<img class="mi-about-hero__cloud mi-about-hero__cloud--right" src="{THEME}/images/pages/about/cloud.webp" width="4096" height="1668" alt="" decoding="async">
				<img class="mi-about-hero__cloud mi-about-hero__cloud--bottom" src="{THEME}/images/pages/about/cloud.webp" width="4096" height="1668" alt="" decoding="async">

				<div class="mi-about-hero__statues" data-about-hero-statues>
					<div class="mi-about-hero__statue mi-about-hero__statue--left" data-about-hero-statue="left">
						<img src="{THEME}/images/pages/about/statues-left.webp" width="768" height="1376" alt="" decoding="async">
					</div>
					<div class="mi-about-hero__statue mi-about-hero__statue--center" data-about-hero-statue="center">
						<img src="{THEME}/images/pages/about/statue-center.webp" width="192" height="344" alt="" decoding="async">
					</div>
					<div class="mi-about-hero__statue mi-about-hero__statue--right" data-about-hero-statue="right">
						<img src="{THEME}/images/pages/about/statues-right.webp" width="768" height="1376" alt="" decoding="async">
					</div>
				</div>
			</div>

			<nav class="mi-about-breadcrumb" aria-label="Хлебные крошки">
				<a href="{THEME}/../../">Главная</a>
				<span aria-hidden="true">/</span>
				<span aria-current="page">{description}</span>
			</nav>

			{custom category="6" template="modules/about-page-hero" limit="1" order="date" sort="asc" cache="no"}
		</div>
	</section>

	<section class="mi-about-team" aria-labelledby="mi-about-team-title" data-about-team>
		<div class="mi-about-team__canvas">
			{custom category="6" template="modules/about-page-team-intro" limit="1" order="date" sort="asc" cache="no"}

			<div class="mi-about-team__stage" aria-live="polite">
				{custom category="10" template="modules/about-page-team-item" limit="8" order="date" sort="asc" cache="no"}
			</div>

			{include file="modules/about-page-team-controls.tpl"}
		</div>
	</section>

	<section class="mi-about-process" aria-labelledby="mi-about-process-title" data-about-process>
		<div class="mi-about-process__canvas">
			{custom category="6" template="modules/about-page-process" limit="1" order="date" sort="asc" cache="no"}
		</div>
	</section>

	<section class="mi-about-reviews" aria-labelledby="mi-about-reviews-title">
		<div class="mi-about-reviews__canvas">
			{custom category="6" template="modules/about-page-reviews-heading" limit="1" order="date" sort="asc" cache="no"}
			<div class="mi-about-reviews__slider" data-reviews-swiper data-lenis-prevent-touch>
				<ul class="mi-about-reviews__list" aria-label="Отзывы клиентов">
					{custom category="11" template="modules/about-page-review-item" limit="4" order="date" sort="asc" cache="no"}
				</ul>
			</div>
		</div>
	</section>

	<section class="mi-about-gratitude" aria-labelledby="mi-about-gratitude-title">
		<div class="mi-about-gratitude__canvas">
			<picture>
				<source media="(max-width: 991px)" srcset="{THEME}/images/img.png" type="image/png">
				<img class="mi-about-gratitude__shelves" src="{THEME}/images/pages/about/shelves.webp" width="3062" height="2054" alt="" loading="lazy" decoding="async" aria-hidden="true">
			</picture>
			{custom category="6" template="modules/about-page-gratitude-heading" limit="1" order="date" sort="asc" cache="no"}
			<ul class="mi-about-gratitude__letters">
				{custom category="13" template="modules/about-page-certificate-item" limit="9" order="date" sort="asc" cache="no"}
			</ul>
		</div>
	</section>

	<section class="mi-about-cta" aria-labelledby="mi-about-cta-title">
		<div class="mi-about-cta__canvas">
			<img class="mi-about-cta__hand mi-about-cta__hand--left" src="{THEME}/images/pages/about/hands.webp" width="1408" height="768" alt="" loading="lazy" decoding="async" aria-hidden="true">
			<img class="mi-about-cta__hand mi-about-cta__hand--right" src="{THEME}/images/pages/about/hands.webp" width="1408" height="768" alt="" loading="lazy" decoding="async" aria-hidden="true">
			{custom category="6" template="modules/about-page-cta" limit="1" order="date" sort="asc" cache="no"}
		</div>
	</section>
</div>

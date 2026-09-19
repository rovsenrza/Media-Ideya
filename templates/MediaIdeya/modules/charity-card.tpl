{* Charity card — one post of category 11 («Благотворительность: карточки»).
   Full-bleed photo; click opens the original in Fancybox. Used by the About page
   block (limit 4, Swiper on mobile) and by /blagotvoritelnost/ (full grid). *}
<li class="mi-about-reviews__card swiper-slide">
	[xfgiven_about_review_image]
	<a class="mi-about-reviews__media" href="[xfvalue_image_url_about_review_image]" data-fancybox="charity" data-caption="{title}" aria-label="Открыть: {title}" draggable="false">
		<img src="[xfvalue_image_url_about_review_image]" width="400" height="574" alt="[xfvalue_about_review_image_alt]" loading="lazy" decoding="async" draggable="false">
	</a>
	[/xfgiven_about_review_image]
	[xfnotgiven_about_review_image]
	<a class="mi-about-reviews__media mi-about-reviews__media--mock" href="{THEME}/images/articles/card.png" data-fancybox="charity" data-caption="{title}" aria-label="Открыть: {title}" draggable="false">
		<picture>
			<source srcset="{THEME}/images/articles/card.webp" type="image/webp">
			<img src="{THEME}/images/articles/card.png" width="400" height="574" alt="{title}" loading="lazy" decoding="async" draggable="false">
		</picture>
	</a>
	[/xfnotgiven_about_review_image]
</li>

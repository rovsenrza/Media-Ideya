<article class="mi-article-card">
	<a class="mi-article-card__media" href="{full-link}">
		[xfgiven_image]
		<img src="[xfvalue_image_url_image]" alt="{title}" width="547" height="329" loading="lazy" decoding="async" draggable="false">
		[/xfgiven_image]
		[xfnotgiven_image]
		<picture>
			<source srcset="{THEME}/images/articles/card.webp" type="image/webp">
			<img src="{THEME}/images/articles/card.png" alt="{title}" width="547" height="329" loading="lazy" decoding="async" draggable="false">
		</picture>
		[/xfnotgiven_image]
	</a>
	<div class="mi-article-card__body">
		<div class="mi-article-card__meta">
			[tags]
			<span class="mi-article-card__tag">{tags}</span>
			[/tags]
			<time datetime="{date=Y-m-d}">[day-news]{date=d.m.Y}[/day-news]</time>
		</div>
		<h3 class="mi-article-card__title">
			<a href="{full-link}">{title}</a>
		</h3>
	</div>
</article>

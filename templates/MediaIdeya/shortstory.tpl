<article class="mi-article-card mi-article-card--list[fixed] is-fixed[/fixed]">
	[not-group=5]
	<div class="mi-card__admin">
		[add-favorites]<span title="{{Add to favorites}}">★</span>[/add-favorites]
		[del-favorites]<span title="{{Remove from favorites}}">☆</span>[/del-favorites]
		[edit]<span title="{{Edit}}">{{Edit}}</span>[/edit]
	</div>
	[/not-group]

	<a class="mi-article-card__media" href="{full-link}">
		[xfgiven_image]
		<img src="[xfvalue_image_url_image]" alt="{title}" width="547" height="329" loading="lazy" decoding="async">
		[/xfgiven_image]
		[xfnotgiven_image]
		<picture>
			<source srcset="{THEME}/images/articles/card.webp" type="image/webp">
			<img src="{THEME}/images/articles/card.png" alt="{title}" width="547" height="329" loading="lazy" decoding="async">
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
		<div class="mi-article-card__excerpt">{short-story limit="200"}</div>
	</div>
</article>

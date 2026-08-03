<article class="mi-card mi-card--short[fixed] is-fixed[/fixed]">
	[not-group=5]
	<div class="mi-card__admin">
		[add-favorites]<span title="{{Add to favorites}}">★</span>[/add-favorites]
		[del-favorites]<span title="{{Remove from favorites}}">☆</span>[/del-favorites]
		[edit]<span title="{{Edit}}">{{Edit}}</span>[/edit]
	</div>
	[/not-group]

	<a class="mi-card__media" href="{full-link}">
		[xfgiven_image]<img src="[xfvalue_image]" alt="{title}" width="547" height="308" loading="lazy" decoding="async">[/xfgiven_image]
	</a>

	<div class="mi-card__body">
		<h2 class="mi-card__title">
			<a href="{full-link}">{title}</a>
		</h2>
		<div class="mi-card__excerpt">
			{short-story}
		</div>
		<div class="mi-card__meta">
			<time datetime="{date=Y-m-d}">[day-news]{date}[/day-news]</time>
			<span class="mi-card__cat">{link-category}</span>
		</div>
	</div>
</article>

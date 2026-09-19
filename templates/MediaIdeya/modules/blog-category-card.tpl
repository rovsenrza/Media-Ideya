{* Blog rubric card — {catmenu id="1" subcat="only" template="modules/blog-category-card"}.
   Same look as the article card; cover = category icon (admin → Категории → Иконка),
   mock cover until one is set. *}
[item]
<article class="mi-article-card mi-article-card--list mi-article-card--rubric">
	<a class="mi-article-card__media" href="{url}" aria-hidden="true" tabindex="-1">
		[cat-icon]<img src="{icon}" alt="{name}" width="547" height="329" loading="lazy" decoding="async" draggable="false">[/cat-icon]
		[not-cat-icon]
		<picture>
			<source srcset="{THEME}/images/articles/card.webp" type="image/webp">
			<img src="{THEME}/images/articles/card.png" alt="{name}" width="547" height="329" loading="lazy" decoding="async" draggable="false">
		</picture>
		[/not-cat-icon]
	</a>
	<div class="mi-article-card__body">
		<div class="mi-article-card__meta">
			<span class="mi-article-card__tag">Рубрика</span>
			<span>Статей: {news-count}</span>
		</div>
		<h3 class="mi-article-card__title">
			<a href="{url}">{name}</a>
		</h3>
		[description]<div class="mi-article-card__excerpt">{description}</div>[/description]
	</div>
</article>
[/item]

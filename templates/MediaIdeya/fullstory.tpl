<article class="mi-article[fixed] is-fixed[/fixed]">
	[not-group=5]
	<div class="mi-article__admin">
		[add-favorites]<span title="{{Add to favorites}}">★</span>[/add-favorites]
		[del-favorites]<span title="{{Remove from favorites}}">☆</span>[/del-favorites]
		[edit]<span title="{{Edit}}">{{Edit}}</span>[/edit]
	</div>
	[/not-group]

	<header class="mi-article__header">
		<h1 class="mi-article__title">{title}</h1>
		<div class="mi-article__meta">
			<span class="mi-article__author">{author}</span>
			<time datetime="{date=Y-m-d}">[day-news]{date}[/day-news]</time>
			<span class="mi-article__cat">{link-category}</span>
			<span class="mi-article__views">{views}</span>
		</div>
	</header>

	<div class="mi-article__content share-content">
		{full-story}
		[edit-date]
		<p class="mi-article__edit">
			{{News article is edited by}}: <b>{editor}</b> — {edit-date}
			[edit-reason]<br>{{Reason}}: {edit-reason}[/edit-reason]
		</p>
		[/edit-date]
	</div>

	{pages}

	<nav class="mi-article__nav" aria-label="Pagination">
		[prev-url]<a class="mi-btn mi-btn--ghost" href="{prev-url}">{{Prev}}</a>[/prev-url]
		[next-url]<a class="mi-btn mi-btn--ghost" href="{next-url}">{{Next}}</a>[/next-url]
	</nav>
</article>

<section class="mi-comments" id="comments">
	[comments]<h2 class="mi-comments__title">{{Comments}} <span>{comments-num}</span></h2>[/comments]
	<div class="mi-comments__list">{comments}</div>
	{navigation}
	{addcomments}
</section>

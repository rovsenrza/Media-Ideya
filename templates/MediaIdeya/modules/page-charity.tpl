{* Благотворительность — statue banner + charity card grid *}
<div class="mi-charity-page">
	<section class="mi-statue-banner" aria-labelledby="mi-charity-title">
		<div class="mi-statue-banner__canvas">
			{include file="modules/statue-banner.tpl"}

			<nav class="mi-statue-banner__breadcrumb" aria-label="Хлебные крошки">
				<a href="{THEME}/../../">Главная</a>
				<span aria-hidden="true">/</span>
				<span aria-current="page">Благотворительность</span>
			</nav>

			<h1 class="mi-statue-banner__title" id="mi-charity-title">Благотворительность</h1>
		</div>
	</section>

	{* Cards come from category 11 («Благотворительность: карточки») — the same posts
	   as the About page block. Category 13 posts (gratitude letters) feed the About
	   «Нас благодарят» shelves only. *}
	<section class="mi-charity-feed" aria-label="Благотворительные проекты">
		<div class="mi-charity-feed__inner">
			<ul class="mi-charity-grid" aria-label="Благотворительные проекты">
				{custom category="11" template="modules/charity-card" limit="120" order="date" sort="asc" cache="no"}
			</ul>
		</div>
	</section>
</div>

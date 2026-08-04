<!DOCTYPE html>
<html lang="ru"[available=lostpassword|register] class="page-form"[/available]>
<head>
	{headers}
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="format-detection" content="telephone=no">
	<link rel="shortcut icon" href="{THEME}/images/favicon.ico">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,300;8..144,500&display=swap">

	{* Shared CSS — bütün səhifələr *}
	<link rel="stylesheet" href="{THEME}/css/base/tokens.css">
	<link rel="stylesheet" href="{THEME}/css/base/reset.css">
	<link rel="stylesheet" href="{THEME}/css/base/typography.css">
	<link rel="stylesheet" href="{THEME}/css/layout/shell.css">
	<link rel="stylesheet" href="{THEME}/css/layout/breakpoints.css">
	<link rel="stylesheet" href="{THEME}/css/components/button.css">
	<link rel="stylesheet" href="{THEME}/css/components/header.css">
	<link rel="stylesheet" href="{THEME}/css/components/section-title.css">
	<link rel="stylesheet" href="{THEME}/css/components/reveal.css">
	<link rel="stylesheet" href="{THEME}/css/components/card.css">
	<link rel="stylesheet" href="{THEME}/css/components/footer.css">
	<link rel="stylesheet" href="{THEME}/css/engine.css">

	{* Page-specific CSS — yalnız lazım olan yüklənir *}
	[available=main]
	<link rel="stylesheet" href="{THEME}/css/components/hero.css">
	<link rel="stylesheet" href="{THEME}/css/components/services.css">
	<link rel="stylesheet" href="{THEME}/css/components/about.css">
	<link rel="stylesheet" href="{THEME}/css/components/clients.css">
	<link rel="stylesheet" href="{THEME}/css/components/faq.css">
	<link rel="stylesheet" href="{THEME}/css/components/articles.css">
	<link rel="stylesheet" href="{THEME}/css/pages/home.css">
	[/available]
	[available=cat|showfull]<link rel="stylesheet" href="{THEME}/css/pages/article.css">[/available]
	[available=static]<link rel="stylesheet" href="{THEME}/css/pages/static.css">[/available]
</head>
<body class="mi-body[available=main] is-home[/available][available=showfull] is-article[/available][available=static] is-static[/available]">

	[not-available=lostpassword|register]
	<div class="mi-page">
		{include file="modules/header.tpl"}

		<main class="mi-main" id="content">
			[available=main]
			{include file="modules/hero.tpl"}
			{include file="modules/services.tpl"}
			{include file="modules/about.tpl"}
			{include file="modules/clients.tpl"}
			{include file="modules/faq.tpl"}
			{include file="modules/articles.tpl"}
			[/available]

			{info}
			[not-available=main]
			[page-title]
			<div class="mi-page-head">
				<h1 class="mi-page-head__title">{page-title}</h1>
				{page-description}
			</div>
			[/page-title]
			[/not-available]

			
		</main>

		{include file="modules/footer.tpl"}
	</div>
	[/not-available]

	[available=lostpassword|register]
	<div class="mi-auth">
		<a class="mi-auth__back" href="/">{{Return to Homepage}}</a>
		<div class="mi-auth__body">
			{info}
			{content}
		</div>
	</div>
	[/available]

	{AJAX}

	<script src="{THEME}/js/main.js" defer></script>
	[available=main]
	<script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/Observer.min.js" defer></script>
	<script src="{THEME}/js/pages/home.js" defer></script>
	[/available]
	[available=showfull]<script src="{THEME}/js/pages/article.js" defer></script>[/available]
	[available=static]<script src="{THEME}/js/pages/static.js" defer></script>[/available]
</body>
</html>

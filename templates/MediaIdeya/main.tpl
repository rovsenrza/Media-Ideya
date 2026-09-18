<!DOCTYPE html>
<html lang="ru"[available=lostpassword|register] class="page-form"[/available]>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="format-detection" content="telephone=no">
	{headers}
	<link rel="icon" href="{THEME}/images/media-ideya-logo.png" type="image/png" sizes="any">
	<link rel="apple-touch-icon" href="{THEME}/images/media-ideya-logo.png">
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
	<link rel="stylesheet" href="{THEME}/css/components/project-contact.css">
	<link rel="stylesheet" href="{THEME}/css/components/preloader.css?v=20260911-6">
	<link rel="stylesheet" href="{THEME}/css/components/request-modal.css">
	<link rel="stylesheet" href="{THEME}/css/components/reveal.css">
	<link rel="stylesheet" href="{THEME}/css/components/card.css">
	<link rel="stylesheet" href="{THEME}/css/components/footer.css?v=20260908-2">
	<link rel="stylesheet" href="{THEME}/css/engine.css">
	[available=feedback]<link rel="stylesheet" href="{THEME}/css/components/inner-page.css">[/available]
	[static=o-kompanii,uslugi]<link rel="stylesheet" href="{THEME}/css/components/inner-page.css">[/static]
	[category=2]<link rel="stylesheet" href="{THEME}/css/components/inner-page.css">[/category]
	<link rel="stylesheet" href="{THEME}/css/pages/cases-page.css">

	{* Page-specific CSS — yalnız lazım olan yüklənir *}
	[available=main]
	<link rel="stylesheet" href="{THEME}/css/components/home-cta.css">
	<link rel="stylesheet" href="{THEME}/css/components/hero.css?v=20260908-2">
	<link rel="stylesheet" href="{THEME}/css/components/services.css">
	<link rel="stylesheet" href="{THEME}/css/components/about.css?v=20260831-2">
	<link rel="stylesheet" href="{THEME}/css/components/clients.css">
	<link rel="stylesheet" href="{THEME}/css/components/faq.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.css">
	<link rel="stylesheet" href="{THEME}/css/components/articles.css?v=20260918-1">
	<link rel="stylesheet" href="{THEME}/css/pages/home.css">
	[/available]
	[available=showfull][not-category=2]<link rel="stylesheet" href="{THEME}/css/pages/article.css?v=20260918-1">[/not-category][/available]
	{* DLE does not nest [not-category] blocks — keep each one flat *}
	[available=cat|search|lastnews|tags|favorites]
	[not-category=2]<link rel="stylesheet" href="{THEME}/css/components/articles.css">[/not-category]
	[not-category=2,13]<link rel="stylesheet" href="{THEME}/css/pages/catalog.css?v=20260908-1">[/not-category]
	[/available]
	[available=cat][category=13]
	<link rel="stylesheet" href="{THEME}/css/components/statue-banner.css?v=20260918-3">
	<link rel="stylesheet" href="{THEME}/css/pages/charity-page.css?v=20260918-2">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.15/dist/fancybox/fancybox.css">
	<link rel="stylesheet" href="{THEME}/css/components/lightbox.css?v=20260918-1">
	[/category][/available]
	[static=o-kompanii]
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.15/dist/fancybox/fancybox.css">
	<link rel="stylesheet" href="{THEME}/css/components/lightbox.css?v=20260918-1">
	[/static]
	[available=showfull][category=13]
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.15/dist/fancybox/fancybox.css">
	<link rel="stylesheet" href="{THEME}/css/components/lightbox.css?v=20260918-1">
	[/category][/available]
	[available=static][not-static=o-kompanii,uslugi]<link rel="stylesheet" href="{THEME}/css/pages/static.css">[/not-static][/available]
	[available=feedback]<link rel="stylesheet" href="{THEME}/css/pages/contact-page.css">[/available]
	[static=o-kompanii]<link rel="stylesheet" href="{THEME}/css/pages/about-page.css?v=20260918-2">[/static]
	[static=uslugi]<link rel="stylesheet" href="{THEME}/css/pages/services-page.css?v=20260914-4">[/static]
</head>
	<body class="mi-body[available=main] is-home[/available][available=showfull] is-article[/available][available=static] is-static[/available][available=cat|search|lastnews|tags|favorites] is-catalog[/available][available=feedback] is-contact-page[/available][static=o-kompanii] is-about-page[/static][static=uslugi] is-services-page[/static][category=2] is-cases-page[/category][available=cat][category=13] is-charity-page[/category][/available]">
	{include file="modules/preloader.tpl"}

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
			[not-category=2,13]
			<div class="mi-page-head">
				<h1 class="mi-page-head__title">{page-title}</h1>
				{page-description}
			</div>
			[/not-category]
			[/page-title]
			[available=cat|search|lastnews|tags|favorites]
			[category=2]
			{include file="modules/page-cases.tpl"}
			[/category]
			[category=13]
			{include file="modules/page-charity.tpl"}
			[/category]
			[not-category=2,13]
			<div class="mi-catalog-page__hero">
				<div class="mi-catalog-page__hero-inner">
					<nav class="mi-catalog-page__breadcrumb" aria-label="Хлебные крошки">
						<a href="{THEME}/../../">Главная</a>
						<span aria-hidden="true">/</span>
						<span aria-current="page">Статьи</span>
					</nav>
					<h1 class="mi-catalog-page__title">Статьи</h1>
					<p class="mi-catalog-page__lead">Идеи, наблюдения и практические подходы к брендингу, маркетингу и digital.</p>
				</div>
			</div>
			<div class="mi-catalog">
				<div class="mi-catalog__grid">{content}</div>
			</div>
			[/not-category]
			[/available]
			[not-available=cat|search|lastnews|tags|favorites]
			{content}
			[/not-available]
			[/not-available]

		</main>

		[available=main]{include file="modules/home-footer.tpl"}[/available]
		[not-available=main]
		[available=feedback]{include file="modules/footer-bar.tpl"}[/available]
		[not-available=feedback]{include file="modules/footer.tpl"}[/not-available]
		[/not-available]
	</div>
	[/not-available]

	[available=lostpassword|register]
	<div class="mi-auth">
		<a class="mi-auth__back" href="{THEME}/../../">{{Return to Homepage}}</a>
		<div class="mi-auth__body">
			{info}
			{content}
		</div>
	</div>
	[/available]

	{AJAX}
	[not-category=2]
	{custom category="2" template="modules/case-detail" limit="20" order="date" sort="desc" cache="no"}
	[/not-category]
	{include file="modules/request-modal.tpl"}

	<script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/lenis@1.3.4/dist/lenis.min.js" defer></script>
	<script src="{THEME}/js/components/preloader.js?v=20260911-1" defer></script>
	<script src="{THEME}/js/main.js" defer></script>
	<script src="{THEME}/js/components/phone-mask.js" defer></script>
	<script src="{THEME}/js/components/request-modal.js" defer></script>
	[available=main]
	<script src="{THEME}/js/components/home-cta.js" defer></script>
	<script src="{THEME}/js/components/faq.js" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.js" defer></script>
	<script src="{THEME}/js/components/articles.js?v=20260918-1" defer></script>
	<script src="{THEME}/js/pages/home.js?v=20260909-1" defer></script>
	[/available]
	[available=showfull]<script src="{THEME}/js/pages/article.js" defer></script>[/available]
	[available=static][not-static=o-kompanii,uslugi]<script src="{THEME}/js/pages/static.js" defer></script>[/not-static][/available]
	[available=feedback]<script src="{THEME}/js/pages/contact-page.js" defer></script>[/available]
	[static=o-kompanii]
	<script src="{THEME}/js/pages/about-page.js?v=20260910-1" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.15/dist/fancybox/fancybox.umd.js" defer data-fancybox-src></script>
	<script src="{THEME}/js/components/lightbox.js?v=20260918-1" defer></script>
	[/static]
	[available=showfull][category=13]
	<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.15/dist/fancybox/fancybox.umd.js" defer data-fancybox-src></script>
	<script src="{THEME}/js/components/lightbox.js?v=20260918-1" defer></script>
	[/category][/available]
	[available=cat][category=13]
	<script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1.15/dist/fancybox/fancybox.umd.js" defer data-fancybox-src></script>
	<script src="{THEME}/js/components/lightbox.js?v=20260918-1" defer></script>
	[/category][/available]
	[static=uslugi]<script src="{THEME}/js/pages/services-page.js?v=20260914-3" defer></script>[/static]
	<script src="{THEME}/js/pages/cases-page.js" defer></script>
</body>
</html>

<?php
/**
 * Media-Ideya P1 CMS — home blocks, static pages, xfields.
 * HTTP: /scripts/mi-p1-setup.php?key=mi-p1-2026
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$key = $_GET['key'] ?? '';
if ($key !== 'mi-p1-2026') {
	http_response_code(403);
	exit('Forbidden');
}
header('Content-Type: text/plain; charset=utf-8');

require_once ENGINE_DIR . '/classes/plugins.class.php';
if (!isset($db) || !is_object($db)) {
	exit("DB connection failed\n");
}

$log = static function (string $line): void {
	echo $line . "\n";
};

$log('Media-Ideya P1 setup');

function mi_xf(array $pairs): string {
	global $db;
	$parts = [];
	foreach ($pairs as $k => $v) {
		if ($v === '' || $v === null) {
			continue;
		}
		$parts[] = $k . '|' . str_replace('|', '&#124;', (string) $v);
	}
	return $db->safesql(implode('||', $parts));
}

function mi_post_exists(string $alt): bool {
	global $db;
	$alt = $db->safesql($alt);
	$row = $db->super_query("SELECT id FROM " . PREFIX . "_post WHERE alt_name='{$alt}' LIMIT 1");
	return !empty($row['id']);
}

function mi_insert_post(string $autor, int $catId, string $title, string $alt, string $short, string $full, string $xf, int $daysAgo = 0): void {
	global $db, $log;
	if (mi_post_exists($alt)) {
		return;
	}
	$date = date('Y-m-d H:i:s', time() - ($daysAgo * 86400));
	$titleS = $db->safesql($title);
	$altS = $db->safesql($alt);
	$shortS = $db->safesql($short);
	$fullS = $db->safesql($full);
	$descr = $db->safesql(mb_substr(strip_tags($short), 0, 300));
	$db->query("INSERT INTO " . PREFIX . "_post (autor, date, short_story, full_story, xfields, title, descr, keywords, category, alt_name, comm_num, allow_comm, allow_main, approve, fixed, allow_br, symbol, tags, metatitle) VALUES ('{$autor}', '{$date}', '{$shortS}', '{$fullS}', '{$xf}', '{$titleS}', '{$descr}', '{$titleS}', '{$catId}', '{$altS}', '0', '0', '0', '1', '0', '1', '', '', '{$titleS}')");
	$id = $db->insert_id();
	$db->query("INSERT INTO " . PREFIX . "_post_extras (news_id, allow_rate, votes, disable_index, related_ids, access, user_id, disable_search, need_pass, allow_rss, allow_rss_dzen, allowed_country, not_allowed_country) VALUES ('{$id}', '1', '0', '0', '', '', '0', '0', '0', '1', '1', '', '')");
	$db->query("INSERT INTO " . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('{$id}', '{$catId}')");
	$log("  + {$title}");
}

function mi_upsert_static(string $name, string $descr, string $html, string $meta = ''): void {
	global $db, $log;
	$nameS = $db->safesql($name);
	$exists = (int) $db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_static WHERE name='{$nameS}'")['count'];
	$descrS = $db->safesql($descr);
	$tplS = $db->safesql($html);
	$metaS = $db->safesql($meta !== '' ? $meta : $descr);
	$time = time();
	if ($exists) {
		$db->query("UPDATE " . PREFIX . "_static SET descr='{$descrS}', template='{$tplS}', metadescr='{$metaS}', metatitle='{$descrS}', template_folder='MediaIdeya', date='{$time}' WHERE name='{$nameS}'");
		$log("Static updated: {$name}");
		return;
	}
	$db->query("INSERT INTO " . PREFIX . "_static (name, descr, template, allow_br, allow_template, grouplevel, tpl, metadescr, metakeys, template_folder, date, metatitle, allow_count, sitemap, disable_index, disable_search, password) VALUES ('{$nameS}', '{$descrS}', '{$tplS}', '1', '1', 'all', '', '{$metaS}', '{$descrS}', 'MediaIdeya', '{$time}', '{$descrS}', '1', '1', '0', '0', '')");
	$log("Static created: {$name}");
}

$categories = [
	3 => ['name' => 'FAQ', 'alt_name' => 'faq', 'descr' => 'Вопросы и ответы'],
	4 => ['name' => 'Услуги (главная)', 'alt_name' => 'uslugi-home', 'descr' => 'Карточки услуг на главной'],
	5 => ['name' => 'Партнёры', 'alt_name' => 'partnery', 'descr' => 'Логотипы клиентов'],
	6 => ['name' => 'О компании (текст)', 'alt_name' => 'home-about', 'descr' => 'Текст блока о компании'],
	7 => ['name' => 'Hero', 'alt_name' => 'hero', 'descr' => 'Строки hero-баннера'],
	8 => ['name' => 'Контакты', 'alt_name' => 'site-contacts', 'descr' => 'Footer и контакты'],
	9 => ['name' => 'Цифры', 'alt_name' => 'home-stats', 'descr' => 'Статистика о компании'],
];

foreach ($categories as $id => $cat) {
	$name = $db->safesql($cat['name']);
	$alt = $db->safesql($cat['alt_name']);
	$descr = $db->safesql($cat['descr']);
	$exists = (int) $db->super_query("SELECT COUNT(*) AS count FROM " . PREFIX . "_category WHERE id='{$id}'")['count'];
	if ($exists) {
		$db->query("UPDATE " . PREFIX . "_category SET parentid='0', posi='{$id}', name='{$name}', alt_name='{$alt}', descr='{$descr}', keywords='{$name}', metatitle='{$name}', fulldescr='{$descr}', active='1', disable_main='1', disable_search='1' WHERE id='{$id}'");
	} else {
		$db->query("INSERT INTO " . PREFIX . "_category (id, parentid, posi, name, alt_name, descr, keywords, metatitle, fulldescr, active, disable_main, disable_search) VALUES ('{$id}', '0', '{$id}', '{$name}', '{$alt}', '{$descr}', '{$name}', '{$name}', '{$descr}', '1', '1', '1')");
	}
	$log("Category {$id}: {$cat['name']}");
}

$xfields = [
	'fields' => [
		'image' => [
			'name' => 'image', 'description' => 'Обложка карточки', 'hint' => '547×329',
			'type' => 'image', 'category' => '', 'group' => '', 'default' => '', 'not_required' => 1,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'image_size' => '547x329', 'image_max_size' => '2048',
			'make_watermark' => 0, 'make_thumb' => 1, 'thumb_size' => '547x329', 'image_sizes' => '547x329',
			'use_opengraph' => 1, 'image_side' => 0, 'thumb_side' => 0, 'max_images' => '', 'storage' => -1,
			'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '', 'files_ext' => '',
			'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '', 'condition' => '',
		],
		'lead' => [
			'name' => 'lead', 'description' => 'Тег на карточке', 'hint' => 'маркетинг',
			'type' => 'text', 'category' => '', 'group' => '', 'default' => '', 'not_required' => 1,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '40', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'client_logo' => [
			'name' => 'client_logo', 'description' => 'Логотип партнёра', 'hint' => 'PNG/SVG на прозрачном фоне',
			'type' => 'image', 'category' => '5', 'group' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'image_size' => '231x80', 'image_max_size' => '512',
			'make_watermark' => 0, 'make_thumb' => 0, 'thumb_size' => '', 'image_sizes' => '231x80',
			'use_opengraph' => 0, 'image_side' => 0, 'thumb_side' => 0, 'max_images' => '', 'storage' => -1,
			'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '', 'files_ext' => '',
			'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '', 'condition' => '',
		],
		'service_slide' => [
			'name' => 'service_slide', 'description' => 'Номер слайда (1–7)', 'hint' => 'Если нет своего изображения',
			'type' => 'text', 'category' => '4', 'group' => '', 'default' => '1', 'not_required' => 1,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '1', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'service_bullets' => [
			'name' => 'service_bullets', 'description' => 'Список преимуществ (HTML li)', 'hint' => '<li>пункт</li>',
			'type' => 'textarea', 'category' => '4', 'group' => '', 'default' => '', 'not_required' => 1,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'service_link' => [
			'name' => 'service_link', 'description' => 'Ссылка «Узнать подробнее»', 'hint' => '/uslugi.html',
			'type' => 'text', 'category' => '4', 'group' => '', 'default' => '/uslugi.html', 'not_required' => 1,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '255', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'service_image' => [
			'name' => 'service_image', 'description' => 'Изображение услуги', 'hint' => '860×740',
			'type' => 'image', 'category' => '4', 'group' => '', 'default' => '', 'not_required' => 1,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'image_size' => '860x740', 'image_max_size' => '4096',
			'make_watermark' => 0, 'make_thumb' => 1, 'thumb_size' => '860x740', 'image_sizes' => '860x740',
			'use_opengraph' => 0, 'image_side' => 0, 'thumb_side' => 0, 'max_images' => '', 'storage' => -1,
			'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '', 'files_ext' => '',
			'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '', 'condition' => '',
		],
		'footer_cta_title' => [
			'name' => 'footer_cta_title', 'description' => 'Footer CTA — заголовок', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => 'Обсудим ваш проект', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '120', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_cta_text' => [
			'name' => 'footer_cta_text', 'description' => 'Footer CTA — текст', 'type' => 'textarea', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_address' => [
			'name' => 'footer_address', 'description' => 'Адрес', 'type' => 'textarea', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => '', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_email' => [
			'name' => 'footer_email', 'description' => 'Email', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => 'info@mediaideya.ru', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '120', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_phone' => [
			'name' => 'footer_phone', 'description' => 'Телефон (отображение)', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '', 'default' => '+7 (953) 284-32-00', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '40', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
		'footer_phone_link' => [
			'name' => 'footer_phone_link', 'description' => 'Телефон (tel: ссылка)', 'type' => 'text', 'category' => '8',
			'group' => '', 'hint' => '+79532843200', 'default' => '+79532843200', 'not_required' => 0,
			'allow_add_usergroups' => '', 'use_as_links' => 0, 'use_editor' => 0, 'safe_mode' => 0,
			'min' => '', 'max' => '20', 'allow_multi' => 0, 'select_separator' => '', 'links_separator' => '',
			'files_ext' => '', 'file_max_size' => '', 'is_public' => 0, 'max_files' => '', 'max_size' => '',
			'condition' => '', 'make_watermark' => 0, 'make_thumb' => 0, 'image_size' => '', 'image_max_size' => '',
			'thumb_size' => '', 'image_sizes' => '', 'use_opengraph' => 0, 'image_side' => '', 'thumb_side' => '',
			'max_images' => '', 'storage' => '',
		],
	],
	'groups' => [],
];

file_put_contents(
	ENGINE_DIR . '/data/xfields.json',
	json_encode($xfields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
	LOCK_EX
);
@unlink(ENGINE_DIR . '/cache/system/xfields.php');
$log('XFields updated');

$admin = $db->super_query("SELECT name FROM " . USERPREFIX . "_users WHERE user_group='1' LIMIT 1");
$autor = $db->safesql($admin['name'] ?? 'admin');

$log('Seed: Hero');
mi_insert_post($autor, 7, 'Рекламное агентство', 'hero-line-1', 'Рекламное агентство', 'Рекламное агентство', '', 30);
mi_insert_post($autor, 7, 'с экспертизой рынка', 'hero-line-2', 'с экспертизой рынка', 'с экспертизой рынка', '', 29);
mi_insert_post($autor, 7, 'Cross-marketing', 'hero-line-3', 'Cross-marketing', 'Cross-marketing', '', 28);

$log('Seed: FAQ');
$faqs = [
	['Какие сроки на запуск кампании?', 'faq-1', 'Первые результаты по медиапланированию — в течение 2–3 недель, комплексные кросс-маркетинговые проекты обсуждаем индивидуально.'],
	['С чего начинается работа?', 'faq-2', 'Начинаем с брифа и анализа задач: цели, аудитория, каналы и бюджет. После этого предлагаем стратегию и план запуска.'],
	['Сколько стоит продвижение?', 'faq-3', 'Стоимость зависит от формата, охвата и длительности кампании. После брифа даём прозрачную смету без скрытых расходов.'],
	['Работаете ли с нашей нишей?', 'faq-4', 'Да — опыт в разных нишах и сегментах. Если ниша новая для нас, быстро погружаемся и подключаем профильных специалистов.'],
	['Что если реклама не сработает?', 'faq-5', 'Строим работу на метриках и итерациях: тестируем гипотезы, корректируем креативы и каналы, чтобы выйти на результат.'],
];
foreach ($faqs as $i => $faq) {
	mi_insert_post($autor, 3, $faq[0], $faq[1], $faq[2], $faq[2], '', 20 - $i);
}

$log('Seed: Services');
$services = [
	['On-packing', 'service-on-packing', 'Специализация нашей компании. Изменение дизайна упаковки продукции с целью проведения стимулирующего мероприятия с&nbsp;дальнейшим розыгрышем ценных призов.', '1', '<li>нанесение оффера партнера с целью привлечения новой аудитории.</li><li>проведение внутренней акции производителя с&nbsp;целью повышения продаж.</li>'],
	['Product-placement', 'service-product-placement', 'Органичное интегрирование товаров, услуг или брендов в сюжет фильмов, сериалов, шоу и даже видеоигр. В отличие от прямых рекламных роликов, она выглядит естественной частью повествования.', '2', ''],
	['Лицензионные продукты', 'service-licensing', 'Сотрудничество между брендами и правообладателями популярного контента (кино, игр, аниме, комиксов), где компания получает официальное разрешение использовать чужие интеллектуальные свойства для продвижения своих товаров или услуг.', '3', ''],
	['Геймификация', 'service-gamification', 'Внедрение игровых механик (очки, уровни, награды, соревнования) в&nbsp;неигровые процессы, чтобы повысить вовлеченность и мотивацию аудитории.', '4', ''],
	['QSR сегмент (сети быстрого питания, кофеен и др)', 'service-qsr', 'Это очень популярный и эффективный канал для продвижения других брендов и продуктов.', '5', ''],
	['Разработка рекламных роликов', 'service-video', 'Короткие или длинные видеофильмы, созданные для продвижения бренда, товара, услуги, мероприятия или идей.', '6', ''],
	['Работа с блогерами', 'service-bloggers', 'Сотрудничество с блогерами и авторами контента для продвижения товаров или услуг через их площадки.', '7', ''],
];
foreach ($services as $i => $s) {
	$xf = mi_xf(['service_slide' => $s[3], 'service_bullets' => $s[4], 'service_link' => '/uslugi.html']);
	mi_insert_post($autor, 4, $s[0], $s[1], $s[2], $s[2], $xf, 15 - $i);
}

$log('Seed: Partners');
for ($p = 1; $p <= 21; $p++) {
	mi_insert_post($autor, 5, 'Партнёр ' . $p, 'partner-' . $p, '', '', '', 10 - ($p % 10));
}

$log('Seed: About');
mi_insert_post($autor, 6, 'О компании', 'about-text', 'Мы&nbsp;специализируемся на&nbsp;брендировании продуктов от&nbsp;крупнейших производителей нашей страны и&nbsp;СНГ. За плечами — команда, которая прошла через десятки ниш и&nbsp;знает, где реклама действительно продаёт, а где просто расходует бюджет.', 'Мы&nbsp;специализируемся на&nbsp;брендировании продуктов от&nbsp;крупнейших производителей нашей страны и&nbsp;СНГ. За плечами — команда, которая прошла через десятки ниш и&nbsp;знает, где реклама действительно продаёт, а где простo расходует бюджет.', '', 40);
$aboutShort = $db->safesql('Мы&nbsp;специализируемся на&nbsp;брендировании продуктов от&nbsp;крупнейших производителей нашей страны и&nbsp;СНГ. За плечами — команда, которая прошла через десятки ниш и&nbsp;знает, где реклама действительно продаёт, а где просто расходует бюджет.');
$db->query("UPDATE " . PREFIX . "_post SET short_story='{$aboutShort}', full_story='{$aboutShort}' WHERE alt_name='about-text'");
$stats = [['7+', 'stat-years-1', 'лет на рынке'], ['50+', 'stat-projects', 'проектов'], ['7+', 'stat-years-2', 'лет на рынке']];
foreach ($stats as $i => $st) {
	mi_insert_post($autor, 9, $st[0], $st[1], $st[2], $st[2], '', 35 - $i);
}

$log('Seed: Contacts');
$contactsXf = mi_xf([
	'footer_cta_title' => 'Обсудим ваш проект',
	'footer_cta_text' => 'Оставьте заявку — перезвоним и разберём, что сработает именно для вас',
	'footer_address' => "г. Брянск\nПроспект Станке Димитрова 54А,",
	'footer_email' => 'info@mediaideya.ru',
	'footer_phone' => '+7 (953) 284-32-00',
	'footer_phone_link' => '+79532843200',
]);
mi_insert_post($autor, 8, 'Контакты сайта', 'site-contacts', '', '', $contactsXf, 50);

$log('Static pages');
mi_upsert_static('uslugi', 'Наши услуги', '<p>Полный перечень услуг Media Ideya. Редактируйте эту страницу в админке: <strong>Статические страницы → uslugi</strong>.</p>');
mi_upsert_static('o-kompanii', 'О компании', '<p>Media Ideya — рекламное агентство с экспертизой cross-marketing. Редактируйте в админке: <strong>Статические страницы → o-kompanii</strong>.</p>');
mi_upsert_static('politika-privatnosti', 'Политика приватности', '<p>Политика обработки персональных данных ООО «Медиа Идея».</p>');

@unlink(ENGINE_DIR . '/cache/system/category.json');
foreach (glob(ENGINE_DIR . '/cache/*.php') ?: [] as $file) {
	@unlink($file);
}
$log('Cache cleared');
$log('Done.');

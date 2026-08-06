<?php
/**
 * Media-Ideya P2 — editor experience: lean admin, editor role, guide, backup.
 * HTTP: /scripts/mi-p2-setup.php?key=mi-p2-2026
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$key = $_GET['key'] ?? '';
if ($key !== 'mi-p2-2026') {
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

$log('Media-Ideya P2 setup');

function mi_config_set(string $key, string $value): bool {
	$path = ENGINE_DIR . '/data/config.php';
	if (!is_readable($path) || !is_writable($path)) {
		return false;
	}
	$src = file_get_contents($path);
	if ($src === false) {
		return false;
	}
	$pattern = "/'" . preg_quote($key, '/') . "'\\s*=>\\s*'[^']*'/";
	$replacement = "'" . $key . "' => '" . addslashes($value) . "'";
	$new = preg_replace($pattern, $replacement, $src, 1, $count);
	if (!$count || $new === null) {
		return false;
	}
	return file_put_contents($path, $new) !== false;
}

/* ——— 1. Disable unused front modules ——— */
$configFlags = [
	'allow_banner' => '0',
	'allow_votes' => '0',
	'rss_informer' => '0',
	'short_rating' => '0',
	'allow_registration' => '0',
];

$log('Config: disable banners, votes, RSS informer');
foreach ($configFlags as $k => $v) {
	if (mi_config_set($k, $v)) {
		$log("  config {$k} = {$v}");
	} else {
		$log("  WARN: could not set {$k}");
	}
}

/* ——— 2. Editor group (id 3) — news + static + xfields only ——— */
$editorCats = '1,2,3,4,5,6,7,8,9';

$log('User group: Редактор контента (id 3)');
$db->query("UPDATE " . USERPREFIX . "_usergroups SET
	group_name='Редактор контента',
	allow_cats='{$editorCats}',
	allow_adds='1',
	cat_add='{$editorCats}',
	cat_allow_addnews='{$editorCats}',
	allow_admin='1',
	allow_all_edit='1',
	allow_edit='1',
	allow_image_upload='1',
	allow_file_upload='1',
	allow_image='1',
	allow_url='1',
	admin_addnews='1',
	admin_editnews='1',
	admin_static='1',
	admin_xfields='1',
	admin_comments='0',
	admin_categories='0',
	admin_editusers='0',
	admin_wordfilter='0',
	admin_userfields='0',
	admin_editvote='0',
	admin_newsletter='0',
	admin_blockip='0',
	admin_banners='0',
	admin_rss='0',
	admin_iptools='0',
	admin_rssinform='0',
	admin_googlemap='0',
	admin_tagscloud='0',
	admin_complaint='0'
WHERE id='3'");

$log('User group: Главные редакторы (id 2) — news only, no system modules');
$db->query("UPDATE " . USERPREFIX . "_usergroups SET
	admin_static='0',
	admin_xfields='0',
	admin_banners='0',
	admin_editvote='0',
	admin_rssinform='0',
	admin_categories='0',
	admin_editusers='0'
WHERE id='2'");

@unlink(ENGINE_DIR . '/cache/system/usergroup.json');

/* ——— 3. Category hints for editors (descr field in admin) ——— */
$catHints = [
	1 => 'Публичные статьи для блока «Разбираем рынок вслух» и раздела /stat-i/. XFields: Обложка, Тег на карточке.',
	2 => 'Кейсы агентства — раздел /keysy/. XFields: Обложка, Тег на карточке.',
	3 => 'Вопросы FAQ на главной. Заголовок = вопрос, краткий текст = ответ. Первый пункт можно оставить открытым (порядок по дате).',
	4 => 'Карточки услуг на главной (скролл-стек). XFields: слайд, список, ссылка.',
	5 => 'Логотипы партнёров. XFields: client_logo (PNG/WebP на прозрачном фоне).',
	6 => 'Текст блока «О компании» на главной — одна запись about-text.',
	7 => 'Строки hero-баннера на главной (порядок по дате).',
	8 => 'Контакты и footer CTA — одна запись site-contacts. XFields: адрес, email, телефон.',
	9 => 'Цифры в блоке «О компании». Заголовок = число, краткий текст = подпись.',
];

$log('Category editor hints');
foreach ($catHints as $id => $hint) {
	$hintS = $db->safesql($hint);
	$db->query("UPDATE " . PREFIX . "_category SET descr='{$hintS}' WHERE id='{$id}'");
	$log("  cat {$id}");
}

@unlink(ENGINE_DIR . '/cache/system/category.json');

/* ——— 4. Backup folder (DLE dumper target) ——— */
$backupDir = ROOT_DIR . '/backup';
if (!is_dir($backupDir)) {
	mkdir($backupDir, 0755, true);
}
foreach (['index.html', '.htaccess'] as $file) {
	$path = $backupDir . '/' . $file;
	if (!is_file($path)) {
		if ($file === 'index.html') {
			file_put_contents($path, '');
		} else {
			file_put_contents($path, "Order Deny,Allow\nDeny from all\n");
		}
	}
}
$log('Backup dir: /backup/ (DLE dumper)');

/* ——— 5. Editor guide (RU) ——— */
$guidePath = ROOT_DIR . '/docs/media-ideya-editor-guide.ru.md';
if (!is_dir(dirname($guidePath))) {
	mkdir(dirname($guidePath), 0755, true);
}

$adminUrl = 'http://localhost:8888/admin.php';
$configPath = ENGINE_DIR . '/data/config.php';
if (is_readable($configPath)) {
	$configSrc = file_get_contents($configPath);
	if ($configSrc && preg_match("/'http_home_url'\\s*=>\\s*'([^']+)'/", $configSrc, $m)) {
		$adminUrl = rtrim($m[1], '/') . '/admin.php';
	}
}

$guide = <<<MD
# Media Ideya — руководство редактора

Краткая инструкция для группы **«Редактор контента»** (новости + статические страницы).

## Вход

- Адрес: `{$adminUrl}`
- Группа: **Редактор контента** (или **Администраторы** для полного доступа)
- После входа включена двухфакторная аутентификация (2FA)

## Что можно редактировать

| Раздел сайта | Где в админке | Категория |
|--------------|---------------|-----------|
| Статьи на главной и /stat-i/ | **Добавить новость** / **Редактировать новости** | Статьи |
| Кейсы /keysy/ | То же | Кейсы |
| FAQ | **Редактировать новости** → фильтр FAQ | FAQ |
| Услуги (главная) | **Редактировать новости** → Услуги (главная) | Услуги (главная) |
| Партнёры | **Редактировать новости** → Партнёры | Партнёры |
| Текст «О компании» | Запись `about-text` | О компании (текст) |
| Цифры «О компании» | Категория «Цифры» | Цифры |
| Hero-баннер | Категория Hero | Hero |
| Footer / контакты | Запись `site-contacts` | Контакты |
| Страницы /uslugi, /o-kompanii | **Статические страницы** | — |

Подсказки по категориям видны в админке в описании категории.

## Добавить статью

1. **Добавить новость**
2. Категория: **Статьи** (или **Кейсы**)
3. Заполните заголовок, краткое и полное описание
4. **Доп. поля (XFields):**
   - **Обложка** — изображение карточки (547×329)
   - **Тег на карточке** — например «маркетинг»
5. **Опубликовать** → проверьте главную и `/stat-i/`

## Изменить FAQ

1. **Редактировать новости**
2. Категория **FAQ**
3. **Заголовок** = вопрос, **Краткое описание** = ответ
4. Порядок на сайте — по дате (старые сверху). Первый пункт открыт по умолчанию.

## Добавить партнёра

1. **Добавить новость** → категория **Партнёры**
2. Заголовок — название компании
3. XField **client_logo** — логотип PNG/WebP на прозрачном фоне
4. На главной логотипы выводятся сеткой 4→3→4… со скроллом в блоке партнёров

## Статические страницы

**Статические страницы** → `uslugi`, `o-kompanii`, `politika-privatnosti`

Шаблон: MediaIdeya. После правок очистите кеш (см. ниже).

## Резервная копия БД (обязательно перед крупными правками)

1. Войдите как **Администратор**
2. **Инструменты** → **Резервное копирование** (`?mod=dumper`)
3. Метод сжатия: **gzip**
4. **Создать резервную копию**
5. Файл сохранится в `/backup/` на сервере — скачайте его локально

Рекомендация: бэкап раз в неделю и перед обновлением контента на проде.

## Очистка кеша

После изменений на сайте: **Инструменты** → **Управление кешем** → очистить.

## Что отключено на сайте

Баннеры, голосования и RSS-информеры отключены — не используются в дизайне Media Ideya.

## Нужна помощь

Технические настройки (шаблон, CSS, модули) — только **Администраторы**.

MD;

file_put_contents($guidePath, $guide);
$log('Guide: docs/media-ideya-editor-guide.ru.md');

foreach (glob(ENGINE_DIR . '/cache/*.php') ?: [] as $file) {
	@unlink($file);
}
$log('Cache cleared');
$log('Done.');
$log('');
$log('Next: create user in Admin → Пользователи → group «Редактор контента»');
$log('Guide: docs/media-ideya-editor-guide.ru.md');

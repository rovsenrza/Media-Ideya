<?php
/**
 * Media-Ideya P3 — polish: admin branding, category tpl, schema.org, deploy cache.
 * HTTP: /scripts/mi-p3-setup.php?key=mi-p3-2026
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

$key = $_GET['key'] ?? '';
if ($key !== 'mi-p3-2026') {
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

$log('Media-Ideya P3 setup');

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

function mi_config_get(string $key): ?string {
	$path = ENGINE_DIR . '/data/config.php';
	if (!is_readable($path)) {
		return null;
	}
	$src = file_get_contents($path);
	if ($src && preg_match("/'" . preg_quote($key, '/') . "'\\s*=>\\s*'([^']*)'/", $src, $m)) {
		return $m[1];
	}
	return null;
}

/* ——— 1. Schema.org + publisher meta ——— */
$log('Config: schema.org + publisher');
$schemaFlags = [
	'schema_org' => 'NewsArticle',
	'site_type' => 'Organization',
	'pub_name' => 'ООО «Медиа Идея»',
];

foreach ($schemaFlags as $k => $v) {
	if (mi_config_set($k, $v)) {
		$log("  {$k} = {$v}");
	} else {
		$log("  WARN: could not set {$k}");
	}
}

$homeUrl = mi_config_get('http_home_url') ?: 'http://localhost:8888/';
$iconPath = rtrim($homeUrl, '/') . '/templates/MediaIdeya/images/media-ideya-logo.png';
if (mi_config_set('site_icon', $iconPath)) {
	$log("  site_icon = {$iconPath}");
}

/* ——— 2. Category templates (public sections) ——— */
$log('Category templates');
$catTpl = [
	1 => ['short_tpl' => 'shortstory', 'full_tpl' => 'fullstory', 'schema_org' => 'NewsArticle'],
	2 => ['short_tpl' => 'shortstory', 'full_tpl' => 'fullstory', 'schema_org' => 'NewsArticle'],
];

foreach ($catTpl as $id => $tpl) {
	$short = $db->safesql($tpl['short_tpl']);
	$full = $db->safesql($tpl['full_tpl']);
	$schema = $db->safesql($tpl['schema_org']);
	$db->query("UPDATE " . PREFIX . "_category SET short_tpl='{$short}', full_tpl='{$full}', schema_org='{$schema}' WHERE id='{$id}'");
	$log("  cat {$id}: {$tpl['short_tpl']} / {$tpl['full_tpl']}");
}

/* Home-only categories — no public listing templates */
$db->query("UPDATE " . PREFIX . "_category SET short_tpl='', full_tpl='', schema_org='0' WHERE id BETWEEN 3 AND 9");
$log('  cats 3–9: no public tpl (home blocks only)');

@unlink(ENGINE_DIR . '/cache/system/category.json');

/* ——— 3. Verify admin branding asset ——— */
$adminCss = ROOT_DIR . '/templates/MediaIdeya/adminpanel.css';
if (is_file($adminCss)) {
	$log('Admin branding: templates/MediaIdeya/adminpanel.css');
} else {
	$log('WARN: adminpanel.css missing');
}

/* ——— 4. Deploy / cache docs ——— */
$deployPath = ROOT_DIR . '/docs/media-ideya-deploy.ru.md';
$purgeUrl = rtrim($homeUrl, '/') . '/scripts/mi-purge-cache.php?key=mi-purge-2026';

$deploy = <<<MD
# Media Ideya — деплой и кеш

## Перед выкладкой на прод

1. **Бэкап БД:** Admin → Инструменты → Резервное копирование (`?mod=dumper`) → gzip → скачать из `/backup/`
2. **Проверить домен** в Admin → Настройки → Системные:
   - `http_home_url` → `https://ваш-домен.ru/`
   - `site_icon` → полный URL логотипа на проде
   - `pub_name` → `ООО «Медиа Идея»`
   - `schema_org` → `NewsArticle` (уже через P3 setup)
3. **Включить кеш на проде** (не на локалке):
   - `allow_cache` = Да
   - `clear_cache` = по необходимости (часы)

## После деплоя (git pull / файлы)

```bash
curl -s "{$purgeUrl}"
```

Или в браузере: `{$purgeUrl}`

Скрипт очищает:
- `engine/cache/*.php`
- JSON-кеш категорий и групп
- compiled templates

## P3 setup (шаблоны категорий + schema)

```
/scripts/mi-p3-setup.php?key=mi-p3-2026
```

## Чеклист prod

- [ ] `http_home_url` — HTTPS, без слэша в конце + `/` в конфиге DLE
- [ ] Favicon и `site_icon` открываются по URL
- [ ] `display_php_errors` = 0
- [ ] 2FA для админов
- [ ] Редакторы в группе «Редактор контента»
- [ ] Кеш включён, после правок — purge
- [ ] Бэкап по расписанию (еженедельно)

MD;

if (!is_dir(dirname($deployPath))) {
	mkdir(dirname($deployPath), 0755, true);
}
file_put_contents($deployPath, $deploy);
$log('Deploy guide: docs/media-ideya-deploy.ru.md');

/* ——— 5. Link from editor guide ——— */
$editorGuide = ROOT_DIR . '/docs/media-ideya-editor-guide.ru.md';
if (is_file($editorGuide)) {
	$append = "\n## Деплой и кеш\n\nСм. [media-ideya-deploy.ru.md](./media-ideya-deploy.ru.md)\n";
	$content = file_get_contents($editorGuide);
	if ($content !== false && strpos($content, 'media-ideya-deploy.ru.md') === false) {
		file_put_contents($editorGuide, $content . $append);
		$log('Editor guide: deploy link added');
	}
}

foreach (glob(ENGINE_DIR . '/cache/*.php') ?: [] as $file) {
	@unlink($file);
}
$log('Cache cleared');
$log('Done.');

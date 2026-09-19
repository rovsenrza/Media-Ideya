<?php
/**
 * Media-Ideya P8 — blog rubrics + charity card category naming.
 *
 *  1. Renames category 11 («Отзывы») to «Благотворительность: карточки» — its posts
 *     are the cards on /blagotvoritelnost/ and in the About block.
 *  2. Creates three blog rubrics (children of category 1 «Статьи») and moves the
 *     existing posts into them. Rubric pages reuse the catalog layout; /stat-i/
 *     itself lists the rubrics via {catmenu}.
 *
 * Idempotent: existing rubrics (by slug) are reused, posts already in a rubric
 * are left alone. Never deletes anything.
 *
 * CLI:
 *   php scripts/mi-p8-blog-rubrics.php            dry run
 *   php scripts/mi-p8-blog-rubrics.php --apply
 * HTTP (production has no shell):
 *   /scripts/mi-p8-blog-rubrics.php?key=mi-blog-rubrics-2026
 *   /scripts/mi-p8-blog-rubrics.php?key=mi-blog-rubrics-2026&apply=1
 */
declare(strict_types=1);

define('DATALIFEENGINE', true);
define('ROOT_DIR', dirname(__DIR__));
define('ENGINE_DIR', ROOT_DIR . '/engine');

const MI_BLOG_CAT_ID = 1;
const MI_CHARITY_CARDS_CAT_ID = 11;
const MI_CHARITY_CARDS_NAME = 'Благотворительность: карточки';
const MI_CHARITY_CARDS_DESCR = 'Карточки благотворительных проектов: фото в поле «Отзыв: фото или логотип». Показываются на /blagotvoritelnost/ и в блоке «Благотворительность» на странице «О компании».';

/** slug => [name, description, post slugs to move into it] */
const MI_RUBRICS = [
	'kross-marketing' => [
		'Кросс-маркетинг',
		'Партнёрские механики, совместные акции и то, как измерять их эффект.',
		['pochemu-kross-marketing-rabotaet-luchshe', 'chto-izmeriat-v-cross-marketing'],
	],
	'product-placement' => [
		'Product placement',
		'Бренд в кино, сериалах и шоу: форматы интеграций и разбор кейсов.',
		['product-placement-v-kino-i-serialah'],
	],
	'upakovka' => [
		'Упаковка и точки входа',
		'On-pack механики, промокоды и упаковка как канал коммуникации.',
		['on-packing-kak-tochka-vhoda'],
	],
];

if (PHP_SAPI === 'cli') {
	$apply = in_array('--apply', $argv, true);
} else {
	if (($_GET['key'] ?? '') !== 'mi-blog-rubrics-2026') {
		http_response_code(403);
		exit('Forbidden');
	}
	header('Content-Type: text/plain; charset=utf-8');
	$apply = ($_GET['apply'] ?? '') === '1';
}

require_once ENGINE_DIR . '/classes/plugins.class.php';
if (!isset($db) || !is_object($db)) {
	exit("DB connection failed\n");
}

$log = static function (string $line = ''): void {
	echo $line . PHP_EOL;
};

function mi_p8_query(string $sql) {
	global $db;
	$result = $db->query($sql, false);
	if ($result === false) {
		$errors = $db->query_errors_list;
		$last = end($errors);
		throw new RuntimeException('DB error: ' . (is_array($last) ? ($last['error'] ?? '?') : '?'));
	}
	return $result;
}

function mi_p8_rows(string $sql): array {
	global $db;
	$rows = [];
	$result = mi_p8_query($sql);
	while ($row = $db->get_row($result)) {
		$rows[] = $row;
	}
	$db->free($result);
	return $rows;
}

$log('Media-Ideya P8 — blog rubrics (' . ($apply ? 'APPLY' : 'dry run') . ')');
$log();

try {
	// 1. Charity cards category name ------------------------------------------
	$cat = mi_p8_rows('SELECT id, name FROM ' . PREFIX . "_category WHERE id='" . MI_CHARITY_CARDS_CAT_ID . "'");
	if (!$cat) {
		throw new RuntimeException('Category ' . MI_CHARITY_CARDS_CAT_ID . ' is missing');
	}
	if ($cat[0]['name'] === MI_CHARITY_CARDS_NAME) {
		$log('[ok] category ' . MI_CHARITY_CARDS_CAT_ID . ' already «' . MI_CHARITY_CARDS_NAME . '»');
	} else {
		$log('[rename] category ' . MI_CHARITY_CARDS_CAT_ID . ' «' . $cat[0]['name'] . '» → «' . MI_CHARITY_CARDS_NAME . '»');
		if ($apply) {
			$nameSql = $db->safesql(MI_CHARITY_CARDS_NAME);
			$descrSql = $db->safesql(MI_CHARITY_CARDS_DESCR);
			mi_p8_query('UPDATE ' . PREFIX . "_category SET name='{$nameSql}', metatitle='{$nameSql}', descr='{$descrSql}', fulldescr='{$descrSql}' WHERE id='" . MI_CHARITY_CARDS_CAT_ID . "'");
		}
	}

	// 2. Rubrics ----------------------------------------------------------------
	$blog = mi_p8_rows('SELECT id, name FROM ' . PREFIX . "_category WHERE id='" . MI_BLOG_CAT_ID . "'");
	if (!$blog) {
		throw new RuntimeException('Blog category ' . MI_BLOG_CAT_ID . ' is missing');
	}
	$posi = (int) (mi_p8_rows('SELECT MAX(posi) AS m FROM ' . PREFIX . '_category')[0]['m'] ?? 0);

	foreach (MI_RUBRICS as $slug => [$name, $descr, $postSlugs]) {
		$slugSql = $db->safesql($slug);
		$existing = mi_p8_rows('SELECT id, parentid, name FROM ' . PREFIX . "_category WHERE alt_name='{$slugSql}'");
		if ($existing) {
			$rubricId = (int) $existing[0]['id'];
			if ((int) $existing[0]['parentid'] !== MI_BLOG_CAT_ID) {
				throw new RuntimeException("Category /{$slug}/ exists (#{$rubricId}) but is not a child of " . MI_BLOG_CAT_ID);
			}
			$log("[ok] rubric #{$rubricId} «{$existing[0]['name']}» /{$slug}/ exists");
		} else {
			$posi++;
			$log("[add] rubric «{$name}» /{$slug}/ under category " . MI_BLOG_CAT_ID);
			$rubricId = 0;
			if ($apply) {
				$nameSql = $db->safesql($name);
				$descrSql = $db->safesql($descr);
				mi_p8_query(
					'INSERT INTO ' . PREFIX . "_category
					(parentid, posi, name, alt_name, descr, keywords, metatitle, fulldescr, active, disable_main, disable_search, disable_comments, short_tpl, full_tpl, schema_org)
					VALUES ('" . MI_BLOG_CAT_ID . "', '{$posi}', '{$nameSql}', '{$slugSql}', '{$descrSql}', '{$nameSql}', '{$nameSql}', '{$descrSql}', '1', '0', '0', '1', '', '', 'NewsArticle')"
				);
				$rubricId = (int) $db->insert_id();
				$log("      → #{$rubricId}");
			}
		}

		foreach ($postSlugs as $postSlug) {
			$postSlugSql = $db->safesql($postSlug);
			$post = mi_p8_rows('SELECT id, category, title FROM ' . PREFIX . "_post WHERE alt_name='{$postSlugSql}'");
			if (!$post) {
				$log("      [skip] post «{$postSlug}» not found");
				continue;
			}
			$postId = (int) $post[0]['id'];
			$cats = array_values(array_filter(array_map('intval', explode(',', (string) $post[0]['category']))));
			if ($rubricId && in_array($rubricId, $cats, true)) {
				$log("      [ok] #{$postId} «{$post[0]['title']}» already in rubric");
				continue;
			}
			$log("      [move] #{$postId} «{$post[0]['title']}» → rubric" . ($rubricId ? " #{$rubricId}" : ''));
			if ($apply && $rubricId) {
				$newCats = array_values(array_unique(array_map(static fn (int $c): int => $c === MI_BLOG_CAT_ID ? $rubricId : $c, $cats)));
				if (!in_array($rubricId, $newCats, true)) {
					$newCats[] = $rubricId;
				}
				$catSql = $db->safesql(implode(',', $newCats));
				mi_p8_query('UPDATE ' . PREFIX . "_post SET category='{$catSql}' WHERE id='{$postId}'");
				mi_p8_query('DELETE FROM ' . PREFIX . "_post_extras_cats WHERE news_id='{$postId}' AND cat_id='" . MI_BLOG_CAT_ID . "'");
				$mapped = mi_p8_rows('SELECT cat_id FROM ' . PREFIX . "_post_extras_cats WHERE news_id='{$postId}' AND cat_id='{$rubricId}'");
				if (!$mapped) {
					mi_p8_query('INSERT INTO ' . PREFIX . "_post_extras_cats (news_id, cat_id) VALUES ('{$postId}', '{$rubricId}')");
				}
			}
		}
	}

	// 3. Cache --------------------------------------------------------------------
	if ($apply) {
		$removed = 0;
		foreach (['system', ''] as $dir) {
			$path = ENGINE_DIR . '/cache' . ($dir !== '' ? "/{$dir}" : '');
			foreach (glob($path . '/*') ?: [] as $file) {
				if (is_file($file) && basename($file) !== '.htaccess' && @unlink($file)) {
					$removed++;
				}
			}
		}
		$log();
		$log("[ok] cache purged ({$removed} files)");
	} else {
		$log();
		$log('Dry run — nothing written.');
	}
} catch (Throwable $e) {
	$log('[error] ' . $e->getMessage());
	exit(1);
}

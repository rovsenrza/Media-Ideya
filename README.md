# Media-Ideya

DataLife Engine (DLE) **20.0** əsasında Media-Ideya veb saytı.

## Stack

| Layer | Texnologiya |
|-------|-------------|
| CMS | DataLife Engine 20.0 |
| Backend | PHP |
| Templates | DLE `.tpl` (Smarty-style tags) |
| Assets | `public/` (JS, CSS, editor, fonts) |
| Design | Figma → template implementasiya |

## Repo strukturu

```
Media-Ideya/
├── index.php              # Frontend entry
├── admin.php              # Admin panel entry
├── install.php            # Quraşdırıcı (quraşdırmadan sonra silinməli)
├── cron.php               # Cron jobs
├── engine/                # DLE core
│   ├── init.php           # Bootstrap
│   ├── engine.php         # Request router
│   ├── modules/           # Frontend modullar (show.short, show.full, …)
│   ├── inc/               # Admin panel modulları
│   ├── ajax/              # AJAX endpoint-lər
│   ├── classes/           # PHP class-lər (templates, parse, mysql, …)
│   ├── data/              # Runtime config (git-ignore — install sonrası)
│   └── cache/             # Cache (git-ignore)
├── templates/             # Skin / tema (.tpl) — əsas iş sahəsi
├── public/                # Statik asset-lər
├── language/              # Dil paketləri
├── uploads/               # İstifadəçi yükləmələri
└── backup/                # DB/fayl backup
```

## Lokal quraşdırma

1. PHP 8.x + MySQL/MariaDB tələb olunur.
2. Web root-u bu qovluğa yönləndirin.
3. Brauzerdə `install.php` açın və quraşdırmanı tamamlayın.
4. Quraşdırmadan sonra `install.php` silin.
5. Tema işi: `templates/<skin-adı>/` altında `.tpl` + CSS/JS.

## Development qeydləri

- **Tema dəyişiklikləri** əsasən `templates/` içindədir — core `engine/` fayllarına toxunmayın (upgrade riski).
- Figma dizaynından implementasiya üçün Cursor rule: `.cursor/rules/media-ideya-workflow.mdc`
- Codebase Memory index: project adı `Media-Ideya` (`.codebase-memory/`)

## Alətlər

- **Figma MCP** — dizayn oxuma / sync
- **Codebase Memory MCP** — arxitektura və kod qrafı

## Aktiv skin

`templates/MediaIdeya/` — Figma-dan sıfırdan yazılır.

```
templates/MediaIdeya/
├── main.tpl | shortstory.tpl | fullstory.tpl | static.tpl
├── css/
│   ├── base/          # tokens, reset, typography
│   ├── layout/        # shell
│   ├── components/    # button, card, footer, …
│   └── pages/         # home, article, static (şərtli yüklənir)
└── js/
    ├── main.js
    ├── components/
    └── pages/
```

Figma: [Медиа идея](https://www.figma.com/design/Bt4qOjgEAEywy7uJCnEQsP) — ilkin breakpoint **1920**.

## Status

- [x] DLE 20.0 core yüklənib
- [x] MediaIdeya skin skeleton (təmiz 4 core template + CSS/JS arxitektura)
- [ ] Install tamamlanmayıb (`engine/data/` boş)
- [ ] Figma → home (1920) implementasiya

## License

DLE core SoftNews Media Group copyright-ına tabedir. Layihə məzmunu və custom template Media-Ideya-ya aiddir.

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

## Временный показ stakeholder (Cloudflare Tunnel)

Локально, без аккаунта Cloudflare — quick tunnel `*.trycloudflare.com`.

```bash
./scripts/mi-tunnel-start.sh
# Public URL в консоли → отправить stakeholder
./scripts/mi-tunnel-stop.sh   # вернуть http://localhost:8888/
```

Скрипт сам меняет `http_home_url` и `site_icon` на публичный URL (CSS/JS работают у внешних пользователей).  
Пока tunnel запущен — **MAMP должен работать**. Остановка tunnel восстанавливает локальный URL.

## После деплоя (git pull / файлы)

```bash
curl -s "http://localhost:8888/scripts/mi-purge-cache.php?key=mi-purge-2026"
```

Или в браузере: `http://localhost:8888/scripts/mi-purge-cache.php?key=mi-purge-2026`

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
